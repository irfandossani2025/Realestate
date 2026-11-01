<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyDocumentController extends Controller
{
    public function store(Request $request, Property $property): RedirectResponse
    {
        $hasAccess = $request->user()->companies()->where('companies.id', $property->company_id)->wherePivot('is_active', true)->exists();
        abort_unless($hasAccess, 403);
        $data = $request->validate([
            'document' => ['required', 'file', 'max:15360', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'category' => ['required', Rule::in(['property_title', 'agreement', 'identity', 'floor_plan', 'valuation_report', 'invoice', 'other'])],
            'language' => ['required', Rule::in(['en', 'ar'])],
        ]);
        $file = $data['document'];
        $path = $file->store("companies/{$property->company_id}/properties/{$property->id}/documents");
        $property->documents()->create([
            'company_id' => $property->company_id, 'uploaded_by' => $request->user()->id,
            'category' => $data['category'], 'language' => $data['language'],
            'original_name' => $file->getClientOriginalName(), 'path' => $path,
            'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()), 'is_public' => false,
        ]);

        return redirect()->route('properties.index')->with('success', 'Private document uploaded.');
    }
}
