<?php

namespace App\Http\Controllers;

use App\Models\NcfSequence;
use App\Models\NcfType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NcfSequenceController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage ncf sequence')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $sequences = NcfSequence::with('ncfType')
            ->where('created_by', Auth::user()->creatorId())
            ->get();

        return view('ncf_sequences.index', compact('sequences'));
    }

    public function create()
    {
        if (! Auth::user()->can('create ncf sequence')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $types = NcfType::where('created_by', Auth::user()->creatorId())->where('is_active', true)->pluck('description', 'id');

        return view('ncf_sequences.create', compact('types'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create ncf sequence')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payload = $this->validatePayload($request, true);

        NcfSequence::create($payload + ['created_by' => Auth::user()->creatorId()]);

        return redirect()->route('ncf-sequences.index')->with('success', __('NCF sequence created successfully.'));
    }

    public function edit(NcfSequence $ncf_sequence)
    {
        if (! Auth::user()->can('edit ncf sequence') || $ncf_sequence->created_by !== Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $types = NcfType::where('created_by', Auth::user()->creatorId())->where('is_active', true)->pluck('description', 'id');

        return view('ncf_sequences.edit', compact('ncf_sequence', 'types'));
    }

    public function update(Request $request, NcfSequence $ncf_sequence)
    {
        if (! Auth::user()->can('edit ncf sequence') || $ncf_sequence->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $payload = $this->validatePayload($request, false);

        $ncf_sequence->update($payload);

        return redirect()->route('ncf-sequences.index')->with('success', __('NCF sequence updated successfully.'));
    }

    public function destroy(NcfSequence $ncf_sequence)
    {
        if (! Auth::user()->can('delete ncf sequence') || $ncf_sequence->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ncf_sequence->delete();

        return redirect()->route('ncf-sequences.index')->with('success', __('NCF sequence deleted successfully.'));
    }

    private function validatePayload(Request $request, bool $defaultActive = true): array
    {
        $creatorId = Auth::user()->creatorId();
        $validator = Validator::make($request->all(), [
            'ncf_type_id' => [
                'required',
                Rule::exists('ncf_types', 'id')->where(fn ($query) => $query->where('created_by', $creatorId)),
            ],
            'serie' => 'nullable|string|max:20',
            'start_number' => 'required|integer|min:1',
            'end_number' => 'required|integer|min:1',
            'current_number' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $validator->after(function ($validator) {
            $data = $validator->getData();
            $startNumber = array_key_exists('start_number', $data) ? (int) $data['start_number'] : null;
            $endNumber = array_key_exists('end_number', $data) ? (int) $data['end_number'] : null;
            $currentNumber = array_key_exists('current_number', $data) ? (int) $data['current_number'] : null;
            $validFrom = $data['valid_from'] ?? null;
            $validUntil = $data['valid_until'] ?? null;

            if ($startNumber !== null && $endNumber !== null && $startNumber > $endNumber) {
                $validator->errors()->add('start_number', __('The start number must be lower than the end number.'));
            }

            if (! empty($validFrom) && ! empty($validUntil) && $validFrom > $validUntil) {
                $validator->errors()->add('valid_from', __('The validity start date must be before the end date.'));
            }

            if ($currentNumber !== null && $startNumber !== null && $currentNumber < $startNumber) {
                $validator->errors()->add('current_number', __('The current number must start within the configured range.'));
            }

            if ($currentNumber !== null && $endNumber !== null && $currentNumber > $endNumber) {
                $validator->errors()->add('current_number', __('The current number cannot exceed the end of the range.'));
            }
        });

        $validated = $validator->validate();

        $startNumber = (int) $validated['start_number'];
        $endNumber = (int) $validated['end_number'];
        $currentNumber = array_key_exists('current_number', $validated)
            ? (int) $validated['current_number']
            : $startNumber;

        return [
            'ncf_type_id' => (int) $validated['ncf_type_id'],
            'serie' => $validated['serie'] ?? null,
            'start_number' => $startNumber,
            'end_number' => $endNumber,
            'current_number' => $currentNumber,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : $defaultActive,
        ];
    }
}
