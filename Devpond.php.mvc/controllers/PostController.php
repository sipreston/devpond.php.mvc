<?php

namespace controllers;

use Vision\VisionFramework\Controller;

class PostController extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->view->showSideBar = true;
    }
    public function indexAction($id = null)
    {
        global $connection;
        $id = CheckSql($id);
        $post = $this->GetPost($id);
        $this->view->post = $post;


        IncreasePostViewsCount($id);
        $this->renderView('views/post/index.phtml');
    }
    function GetPost($PostId)
    {
        $post = new Post();
        $query = "SELECT * FROM posts WHERE post_id = $PostId ";
        if(!SessionUserIsAdmin())
        {
            $query .= "AND post_status = 'published'";
        }
        $select_post = ExecuteQuery($query);

        $row = SqlGetRecord($select_post);

        $post->post_title = $row['post_title'];
        $post->post_author = $row['post_author'];
        $post->post_date = $row['post_date'];
        $post->post_image = $row['post_image'];
        $post->post_content = $row['post_content'];
        $post->post_id = $PostId;
        $post->showPost = false;
        if($row['[post_status'] = 'published')
        {
            $post->showPost = true;
        }
        $post->post_author_name = GetUserRealnameById($post->post_author);

        $post->comments = $this->GetComments($PostId);

        return $post;

    }
    public function GetComments($PostId)
    {
        $comments = array();
        $query = "SELECT * FROM comments WHERE comment_post_id = {$PostId} AND comment_status='approved' ";
        $query .= "ORDER BY comment_id DESC";

        $select_all_comments = ExecuteQuery($query);

        while($row = SqlGetRecord($select_all_comments))
        {
            $comment = new Comment();
            $comment->comment_author_id = $row['comment_author'];
            $comment->comment_content = $row['comment_content'];
            $comment->comment_email = $row['comment_email'];
            $comment->comment_id = $row['comment_id'];
            $comment->comment_status = $row['comment_status'];
            $comment->comment_date = $row['comment_date'];

            //Implement when table changes.
            //$comment->comment_author = $row['comment_author'];
            array_push($comments, $comment);

        }

        return $comments;
    }
}