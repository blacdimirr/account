<?php

namespace App\Http\Controllers;

use App\Models\NcfSequence;
use App\Models\NcfType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $this->validatePayload($request);

        NcfSequence::create([
            'ncf_type_id' => $request->ncf_type_id,
            'serie' => $request->serie,
            'start_number' => $request->start_number,
            'end_number' => $request->end_number,
            'current_number' => $request->current_number,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::user()->creatorId(),
        ]);

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

        $this->validatePayload($request, $ncf_sequence->id);

        $ncf_sequence->update([
            'ncf_type_id' => $request->ncf_type_id,
            'serie' => $request->serie,
            'start_number' => $request->start_number,
            'end_number' => $request->end_number,
            'current_number' => $request->current_number,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'is_active' => $request->boolean('is_active', false),
        ]);

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

    private function validatePayload(Request $request, ?int $sequenceId = null): void
    {
        $creatorId = Auth::user()->creatorId();
        $validator = \Validator::make(
            $request->all(),
            [
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
            ]
        );

        $validator->after(function ($validator) use ($request) {
            if ($request->start_number > $request->end_number) {
                $validator->errors()->add('start_number', __('The start number must be lower than the end number.'));
            }

            if (! empty($request->valid_from) && ! empty($request->valid_until) && $request->valid_from > $request->valid_until) {
                $validator->errors()->add('valid_from', __('The validity start date must be before the end date.'));
            }
        });

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            redirect()->back()->with('error', $messages->first())->send();
            exit;
        }
    }
}
