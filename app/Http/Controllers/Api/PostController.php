<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\support\Facades\validator;
use Illuminate\support\Facades\Storage;

class PostController extends Controller
{
    public function index(){

        //get posts
        $posts = Post::latest()->paginate(5);
        
        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    // insert data
    public function store(Request $request){

        // validasi
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required'
        ]);

        //check validasi error
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        //return response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }

    // show data
    public function show(Post $post){
        return new PostResource(true, 'Data Post Ditemukan!', $post);
    }

    // update data
    public function update(Request $request, Post $post){

        //validasi
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        //check jika validasi gagal
        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        //check jika gambar tidak kosong
        if ($request->hasFile('image')){

            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // hapus gambar
            Storage::delete('public/posts/'.$post->image);
            
            // update post dengan gambar baru
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {

            //update tanpa gambar
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        // return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);

    }

    // hapus data
    public function destroy(Post $post){
        // delete image
        Storage::delete('public/posts/'.$post->image);

        // delete post
        $post->delete();

        //return response
        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
