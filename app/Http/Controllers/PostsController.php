<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Post; //to fetch post
use DB;   // for normal sql query

class PostsController extends Controller
{



     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth',['except'=>['index','show']]);
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {  // $posts = Post::all();  //return all the post in the table
        //$posts=Post::orderBy('title','desc')->get(); //order post in descending order
        $posts=Post::orderBy('created_at','desc')->paginate(); 
        return view('posts.index')->with('posts',$posts);
        // $posts=Post::orderBy('title','desc')->take(3)->get(); // limit number of post to 3
        //$posts= DB::select('SELECT * FROM posts'); // how to use sql query
        //return Post::where('title','Post Two')->get();  // to get only post two
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {    //validation
        $this->validate($request,[
            'title' => 'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:1999'   //image should be jpg,and size 2megabytes(max:1999)
        ]);

        // Handle File Upload
        if($request->hasFile('cover_image')){
            // Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore= $filename.'_'.time().'.'.$extension;
            // Upload Image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
		
	  
		
        } else {
            $fileNameToStore = 'noimage.jpg';  // if user dont upload an image it will use thi default image
        }


        //Create Post
        $post =new Post;
        $post->title =$request->input('title');
        $post->body=$request->input('body');
        $post->user_id =auth()->user()->id;
        $post->cover_image =$fileNameToStore;
        $post->save();

        return redirect('/posts')->with('success','Post Created');
        }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post= Post::find($id);
        return view('posts.show')->with('post',$post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        $post= Post::find($id);
              //Check for correct user, can't edit other people's stuff
        if(auth()->user()->id !==$post->user_id){
            return redirect('/posts')->with('error','Unauthorized Page');

        }
        return view('posts.edit')->with('post',$post);
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         //validation
         $this->validate($request,[
            'title' => 'required',
            'body' => 'required'
        ]);

         // Handle File Upload
         if($request->hasFile('cover_image')){
            // Get filename with the extension
            $filenameWithExt = $request->file('cover_image')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            // Filename to store
            $fileNameToStore= $filename.'_'.time().'.'.$extension;
            // Upload Image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
		
	   
    } 

        //Create Post
        $post = Post::find($id);
        $post->title =$request->input('title');
        $post->body=$request->input('body');
        if($request->hasFile('cover_image')){
            $post->cover_image =$fileNameToStore;
        }
        $post->save();

        return redirect('/posts')->with('success','Post Updated'); //post updated is a message 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post =Post::find($id);

         //Check for correct user, can't delete other people's stuff

         if(auth()->user()->id !==$post->user_id){
            return redirect('/posts')->with('error','Unauthorized Page');

        }

        if($post ->cover_image != 'noimage.jpg') {
            //Delete Image
            Storage::delete('public/cover_images/'.$post->cover_image);
        }


        

        $post->delete();
        return redirect('/posts')->with('success','Post Deleted'); //post deleted is a message 
    }
}
