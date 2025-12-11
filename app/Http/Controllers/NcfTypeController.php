<?php

namespace App\Http\Controllers;

use App\Models\NcfType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NcfTypeController extends Controller
{
    public function index()
    {
        if (! Auth::user()->can('manage ncf type')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $types = NcfType::where('created_by', Auth::user()->creatorId())->get();

        return view('ncf_types.index', compact('types'));
    }

    public function create()
    {
        if (! Auth::user()->can('create ncf type')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('ncf_types.create');
    }

    public function store(Request $request)
    {
        if (! Auth::user()->can('create ncf type')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'code' => 'required|string|max:10|unique:ncf_types,code,NULL,id,created_by,' . Auth::user()->creatorId(),
            'description' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        NcfType::create([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::user()->creatorId(),
        ]);

        return redirect()->route('ncf-types.index')->with('success', __('NCF type created successfully.'));
    }

    public function edit(NcfType $ncf_type)
    {
        if (! Auth::user()->can('edit ncf type') || $ncf_type->created_by !== Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('ncf_types.edit', compact('ncf_type'));
    }

    public function update(Request $request, NcfType $ncf_type)
    {
        if (! Auth::user()->can('edit ncf type') || $ncf_type->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'code' => 'required|string|max:10|unique:ncf_types,code,' . $ncf_type->id . ',id,created_by,' . Auth::user()->creatorId(),
            'description' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $ncf_type->update([
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()->route('ncf-types.index')->with('success', __('NCF type updated successfully.'));
    }

    public function destroy(NcfType $ncf_type)
    {
        if (! Auth::user()->can('delete ncf type') || $ncf_type->created_by !== Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $ncf_type->delete();

        return redirect()->route('ncf-types.index')->with('success', __('NCF type deleted successfully.'));
    }
}
