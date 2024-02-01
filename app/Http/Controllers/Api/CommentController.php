<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Category;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');
        if (!in_array($orderColumn, ['id', 'title', 'created_at'])) {
            $orderColumn = 'created_at';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }
        
        $posts = Comment::with(['post','user'])
            ->whereHas('user', function ($query) {
                if (request('user_name')) {
                    $query->where('name', 'ilike', '%' . request('user_name') . '%');
                }
            })
            ->when(!auth()->user()->hasPermissionTo('comment-all'), function ($query) {
                $query->where('us er_id', auth()->id());
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(50);
        return CommentResource::collection($posts);
    }

    public function store(StoreCommentRequest $request)
    {
        $this->authorize('comment-create');

        $validatedData = $request->validated();
        $validatedData['user_id'] = auth()->id();
        $post = Comment::create($validatedData);
        return new CommentResource($post);
    }

    public function show(Comment $post)
    {
        $this->authorize('post-edit');
        if ($post->user_id !== auth()->user()->id && !auth()->user()->hasPermissionTo('comment-all')) {
            return response()->json(['status' => 405, 'success' => false, 'message' => 'You can only edit your own posts']);
        } else {
            return new CommentResource($post);
        }
    }

    public function update(Comment $post, StoreCommentRequest $request)
    {
        $this->authorize('post-edit');
        if ($post->user_id !== auth()->id() && !auth()->user()->hasPermissionTo('comment-all')) {
            return response()->json(['status' => 405, 'success' => false, 'message' => 'You can only edit your own posts']);
        } else {
            $post->update($request->validated());
//            error_log(json_encode($request->categories));

            $category = Category::findMany($request->categories);
            $post->categories()->sync($category);
            return new CommentResource($post);
        }
    }

    public function destroy(Comment $post)
    {
        $this->authorize('post-delete');
        if ($post->user_id !== auth()->id() && !auth()->user()->hasPermissionTo('comment-all')) {
            return response()->json(['status' => 405, 'success' => false, 'message' => 'You can only delete your own posts']);
        } else {
            $post->delete();
            return response()->noContent();
        }
    }

    public function getPosts()
    {
        $posts = Comment::with('categories')->with('media')->latest()->paginate();
        return CommentResource::collection($posts);

    }

    public function getCategoryByPosts($id)
    {
        $posts = Comment::whereRelation('categories', 'category_id', '=', $id)->paginate();

        return CommentResource::collection($posts);
    }

    public function getPost($id)
    {
        return Comment::with('categories', 'user', 'media')->findOrFail($id);
    }
}
