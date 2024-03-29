<?php

namespace App\Http\Controllers;

use App\Http\Resources\SocialResource;
use App\Models\Settings\Social;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SocialController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $socials = Social::all();
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Social networks retrieved successfully.',
                'data' => SocialResource::collection($socials)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 1,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            $social = Social::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Social network retrieved successfully.',
                'data' => SocialResource::make($social)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 1,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store(Request $request) //: \Illuminate\Http\JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            "icon" => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 2,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
                $file = $request->file('icon');
                $filename = $file->getClientOriginalName();
                $path = Storage::disk('public')->putFileAs('homepage/socials', $file, $filename);

            $social = new Social([
                'name' => $request->name,
                'url' => $request->url,
                'icon' => $path
            ]);
            $social->save();
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Social network saved successfully.',
                'data' => SocialResource::make($social)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 3,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Get the social by ID
            $social = Social::findOrFail($id);

            $is_image = false;

            if ($request->hasFile('icon') && is_file($request->icon)) {
                $is_image = true;
            }

            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string',
                'url' => 'required|string|nullable',
                "icon" => ($is_image) ? 'required|image|mimes:jpeg,png,jpg,svg|max:2048' : 'string',
            ]);

            if ($is_image) {
                $file = $request->file('icon');
                $filename = $file->getClientOriginalName();
                $path = Storage::disk('public')->putFileAs('homepage/socials', $file, $filename);
            } else {
                $path = $request->icon;
            }
            $social->name = $validatedData['name'];
            $social->url = $validatedData['url'];
            $social->icon = $validatedData['icon'];

            $social->save();

            // Return a success response
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Social updated successfully.',
                'data' => SocialResource::make($social)
            ]);

        } catch (\Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'code' => 1,
                'message' => 'Failed to update social.',
                'data' => ['error' => $e->getMessage()]
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            // Get the social by ID
            $social = Social::findOrFail($id);

            // Delete the social's icon file if it exists
            if ($social->icon) {
                Storage::delete('public/homepage/socials/'.$social->icon);
            }

            // Delete the social
            $social->delete();

            // Return a success response
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Social deleted successfully.',
            ]);

        } catch (\Exception $e) {
            // Return an error response
            return response()->json([
                'status' => 'error',
                'code' => 1,
                'message' => 'Failed to delete social.',
                'data' => ['error' => $e->getMessage()]
            ]);
        }
    }
}
