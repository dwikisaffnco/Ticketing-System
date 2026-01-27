<?php

namespace App\Http\Controllers;

use App\Http\Resources\GuideCategoryResource;
use App\Http\Resources\GuideResource;
use App\Models\Guide;
use App\Models\GuideCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuideController extends Controller
{
    // Get all categories with guides
    public function getCategories()
    {
        try {
            $categories = GuideCategory::with('guides')
                ->orderBy('order')
                ->get();

            return response()->json([
                'message' => 'Kategori panduan berhasil diambil',
                'data' => GuideCategoryResource::collection($categories)
            ], 200);
        } catch (Exception $e) {
            Log::error('Get categories error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all guides (admin)
    public function index(Request $request)
    {
        try {
            $query = Guide::with('category');

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('problem', 'like', "%$search%");
                });
            }

            $guides = $query->orderBy('order')->paginate(20);

            return response()->json([
                'message' => 'Panduan berhasil diambil',
                'data' => GuideResource::collection($guides->items()),
                'pagination' => [
                    'total' => $guides->total(),
                    'per_page' => $guides->perPage(),
                    'current_page' => $guides->currentPage(),
                    'last_page' => $guides->lastPage(),
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('Get guides error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get single guide
    public function show($id)
    {
        try {
            $guide = Guide::with('category')->findOrFail($id);

            return response()->json([
                'message' => 'Panduan berhasil diambil',
                'data' => new GuideResource($guide)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Panduan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Create guide
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:guide_categories,id',
            'title' => 'required|string|max:255',
            'problem' => 'required|string',
            'solutions' => 'required|array|min:1',
            'solutions.*' => 'required|string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        try {
            $guide = Guide::create($validated);
            $guide->load('category');

            return response()->json([
                'message' => 'Panduan berhasil dibuat',
                'data' => new GuideResource($guide)
            ], 201);
        } catch (Exception $e) {
            Log::error('Create guide error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update guide
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'exists:guide_categories,id',
            'title' => 'string|max:255',
            'problem' => 'string',
            'solutions' => 'array|min:1',
            'solutions.*' => 'string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        try {
            $guide = Guide::findOrFail($id);
            $guide->update($validated);
            $guide->load('category');

            return response()->json([
                'message' => 'Panduan berhasil diperbarui',
                'data' => new GuideResource($guide)
            ], 200);
        } catch (Exception $e) {
            Log::error('Update guide error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete guide
    public function destroy($id)
    {
        try {
            $guide = Guide::findOrFail($id);
            $guide->delete();

            return response()->json([
                'message' => 'Panduan berhasil dihapus',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            Log::error('Delete guide error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Manage categories
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'order' => 'integer',
        ]);

        try {
            $category = GuideCategory::create($validated);

            return response()->json([
                'message' => 'Kategori berhasil dibuat',
                'data' => new GuideCategoryResource($category)
            ], 201);
        } catch (Exception $e) {
            Log::error('Create category error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'icon' => 'string|max:10',
            'order' => 'integer',
        ]);

        try {
            $category = GuideCategory::findOrFail($id);
            $category->update($validated);

            return response()->json([
                'message' => 'Kategori berhasil diperbarui',
                'data' => new GuideCategoryResource($category)
            ], 200);
        } catch (Exception $e) {
            Log::error('Update category error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyCategory($id)
    {
        try {
            $category = GuideCategory::findOrFail($id);
            $category->delete();

            return response()->json([
                'message' => 'Kategori berhasil dihapus',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            Log::error('Delete category error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
