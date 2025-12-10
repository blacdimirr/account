<?php

namespace App\Http\Controllers;

use App\Models\NcfSequence;
use App\Models\NcfType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $validated = $request->validate([
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

        $startNumber = (int) $validated['start_number'];
        $endNumber = (int) $validated['end_number'];
        $currentNumber = array_key_exists('current_number', $validated)
            ? (int) $validated['current_number']
            : max(0, $startNumber - 1);

        if ($startNumber > $endNumber) {
            throw ValidationException::withMessages([
                'start_number' => __('The start number must be lower than the end number.'),
            ]);
        }

        if (! empty($validated['valid_from']) && ! empty($validated['valid_until']) && $validated['valid_from'] > $validated['valid_until']) {
            throw ValidationException::withMessages([
                'valid_from' => __('The validity start date must be before the end date.'),
            ]);
        }

        $minimumAllowed = max(0, $startNumber - 1);

        if ($currentNumber < $minimumAllowed) {
            throw ValidationException::withMessages([
                'current_number' => __('The current number must start at the previous value in the range.'),
            ]);
        }

        if ($currentNumber > $endNumber) {
            throw ValidationException::withMessages([
                'current_number' => __('The current number cannot exceed the end of the range.'),
            ]);
        }

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
