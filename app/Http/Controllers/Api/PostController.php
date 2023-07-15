<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    //index
    public function index(){
        // get all latest 5 post
        $posts = Post::latest()->paginate(5);

        // return collection of posts as a resource
        return new PostResource(true, 'List data Posts', $posts);
    }

    // post
    public function store(Request $request){
        // validator rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // if vaidator fails
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // response
        return new PostResource(true, 'Data Success Add', $post);
    }

    public function show($id){
        // find by id
        $post = Post::find($id);

        // return response
        return new PostResource(true, 'Detail Data', $post);
    }

    public function update(Request $request, $id){
        // validator rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // if validaton fail
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        // find post by id
        $post = Post::find($id);

        // update with image
        if($request->hasFile('image')){
            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // delete old image
            Storage::delete('public/posts/'.basename($post->image));

            // update process
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }else{
            // update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        // return response
        return new PostResource(true, 'Data updated', $post);
    }

    public function destroy($id){
        // find by id
        $post = Post::find($id);

        // delete image
        Storage::delete('public/posts/'.basename($post->image));

        // delete post
        $post->delete();

        // return response
        return new PostResource(true, 'Data Deleted', null);

    }
}
