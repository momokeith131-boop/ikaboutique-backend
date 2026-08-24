<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController
{
    public function index(Request $request)
    {
        $query = Category::query()->where('is_active', true);

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        $query->orderBy('sort_order');
        $categories = $query->get();

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = Category::with('products', 'children')->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Category retrieved successfully',
            'data' => $category,
        ]);
    }
}
