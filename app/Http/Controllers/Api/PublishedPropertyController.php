<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublishedPropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $properties = Property::query()
            ->where('publication_status', 'published')
            ->whereNotNull('published_at')
            ->select(['id', 'reference', 'property_type', 'purpose', 'title', 'title_ar', 'description', 'description_ar', 'city', 'area', 'bedrooms', 'bathrooms', 'area_sqm', 'sale_price', 'rent_price', 'rent_period', 'published_at'])
            ->latest('published_at')
            ->paginate(min($request->integer('per_page', 12), 50));

        return response()->json($properties);
    }
}
