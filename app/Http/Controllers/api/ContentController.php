<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use App\Models\content;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContentController extends Controller
{
    public function index() {
        $contents = Content::with('documents')->get()->map(function ($content) {
            return [
                'id' => $content->id,
                'from' => $content->from,
                'to' => $content->to,
                'subject' => $content->Subject,
                'description' => $content->Description,
                'documents' => $content->documents->map(function ($document) {
                    return [
                        'id' => $document->id,
                        'name' => $document->name,
                        'url' => route('material', ['folder' => 'Docs', 'item' => pathinfo(basename($document->name),PATHINFO_FILENAME), 'no_cache' => Str::random(4)])
                    ];
                }),
            ];
        });
        return response()->json([
            'contents' => $contents
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'from' => 'required|exists:users,id',
            'to' => 'required|exists:users,id',
            'Subject' => 'required|string',
            'Description' => [
                'string',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count($value);
                    if ($wordCount > 50) {
                        $fail('The ' . $attribute . ' may not be greater than 50 words.');
                    }
                },
            ],
            'Date'=>'required|date',
            'Status' =>'required|string',
            'Docs' => 'required|mimes:png,jpg,jpeg,doc,docx,pdf,xlsx,xls',
        ]);

        $validator->after(function ($validator) use ($request) {
            $totalSize = 0;
            if ($request->hasFile('Docs')) {
                $docs = $request->file('Docs');
                if (!is_array($docs)) {
                    $docs = [$docs];
                }

                if (count($docs) > 5) {
                    $validator->errors()->add('Docs', 'You may not upload more than 5 files.');
                }

                foreach ($request->file('Docs') as $file) {
                    $totalSize += $file->getSize();
                }
                if ($totalSize > 45 * 1024 * 1024) {
                    $validator->errors()->add('Docs', 'The total size of uploaded documents may not exceed 45 MB.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json(['error' => implode(" - ", $validator->errors()->all())], 500);
        }

        DB::beginTransaction();
        try {
            $user = auth::user();

            $content = Content::create([
                'from' => $request->from,
                'to' => $request->to,
                'Subject' => $request->Subject,
                'Description' => $request->Description,
                'Date' => $request->Date,
                'Status' => $request->Status,
                'added_by' => $user->id
            ]);

            if ($request->hasFile('Docs')) {
                $docs = $request->file('Docs');

                foreach ($docs as $file) {
                    $image =Str::random(6);
                    $extension = $file->getClientOriginalExtension();
                    $img_name = $image.'.'.$extension;
                    saveRequestFile($file, "$img_name", "Docs");
                    $doc = Document::create([
                        'content_id'=> $content->id,
                        'name' => $img_name
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to store content', 'message' => $e->getMessage()], 500);
        }




    }

    public function delete(Content $content) {
        // return $content->documents;
        if(!$content)
            return response()->json(['error' => 'content not found']);

        foreach ($content->documents as $document) {
            deleteOldFile("Docs/",$document->name);
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'msg' => 'Deleted successfully'
        ]);
    }

    public function material(Request $request,$folder, $img, $no_cache)
    {
        $paths = findFiles("$folder", "$img");
        if (isset($paths[0]) && $paths[0]) {
            return responseFile($paths[0], "$img");
        }
        return response(['message' => 'not found'], 404);
    }
}
