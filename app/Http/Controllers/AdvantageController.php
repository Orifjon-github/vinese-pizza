<?php

namespace App\Http\Controllers;


use App\Http\Resources\AdvantageResource;
use App\Models\Settings\Advantage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdvantageController extends Controller
{
    public function index()
    {
        $advantages = Advantage::orderBy('num')->get();
        return response()->json([
            'status' => 'success',
            'code' => 0,
            'message' => 'Advantages retrieved successfully.',
            'data' => AdvantageResource::collection($advantages),
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'num' => 'required|integer|unique:advantages,num,',
                'title' => 'required|string|max:255',
                'url' => 'required|file|mimes:jpeg,png,jpg,svg|max:2048'
            ]);

            // Upload the file to storage
            $file = $request->file('url');
            $filename = $file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs('homepage/advantages', $file, $filename);


            // Create the new Advantage model instance
            $advantage = new Advantage([
                'num' => $request->input('num'),
                'title' => $request->input('title'),
                'url' => $path
            ]);

            // Save the new Advantage to the database
            $advantage->save();

            // Return the success response
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Advantage created successfully.',
                'data' => AdvantageResource::make($advantage)
            ]);
        } catch (\Exception $e) {
            // Return the error response
            return response()->json([
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $advantage = Advantage::find($id);

            if (!$advantage) {
                throw new \Exception('Advantage not found.');
            }

            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Advantage retrieved successfully.',
                'data' => AdvantageResource::make($advantage),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $is_image = false;

            if ($request->hasFile('icon') && is_file($request->icon)) {
                $is_image = true;
            }

            $validator = Validator::make($request->all(), [
                'num' => 'required|integer|unique:advantages,num,' . $id,
                'title' => 'required|string|max:255',
                "url" => ($is_image) ? 'required|image|mimes:jpeg,png,jpg,svg|max:2048' : 'string',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $advantage = Advantage::find($id);

            if (!$advantage) {
                throw new \Exception('Advantage not found.');
            }

            // Check if new file has been uploaded
            if ($is_image) {
                $file = $request->file('url');
                $filename = $file->getClientOriginalName();
                $path = Storage::disk('public')->putFileAs('homepage/advantages', $file, $filename);
            } else {
                $path = $request->url;
            }
            $advantage->num = $request->num;
            $advantage->title = $request->title;
            $advantage->url = $request->url;

            $advantage->save();
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Advantage updated successfully.',
                'data' => AdvantageResource::make($advantage),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        $advantage = Advantage::find($id);

        if (!$advantage) {
            throw new \Exception('Advantage not found.');
        }

        try {
            $advantage->delete();
            return response()->json([
                'status' => 'success',
                'code' => 0,
                'message' => 'Advantage removed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to remove advantage.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
