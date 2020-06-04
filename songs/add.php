<?php
/**
 * @file
 * Adds a song to the database
 * Requires user authentification
 */
require 'songParser.php';
require_once("../users/encrypt.php");
require_once '../shared/loadWordpress.php';

if($_POST)
{
    function checkTermExistance($taxonomy,$text)//For creating songauthors and songlanguages
    {
        $exists = term_exists($text, $taxonomy );
        if($exists == NULL)
        {
            wp_insert_term($text,$taxonomy);
        }
    }

    $responseObj = new stdClass();

    $credentials = htmlspecialchars($_POST["credentials"]);
    $name = htmlspecialchars($_POST["name"]);
    $author = htmlspecialchars($_POST["author"]);
    $language = htmlspecialchars($_POST["language"]);
    $songContent = $_POST["songContent"];
    $edit = $_POST["edit"] == "true";

    $decryptionResult = decryptCredentials($credentials);
    if($decryptionResult!=-1)
    {
        $username = $decryptionResult->username;
        $password = $decryptionResult->password;

        //$client = new XMLRPClient("https://www.dorostmladez.cz/xmlrpc.php",$username,$password );

        checkTermExistance("songauthor",$author);
        checkTermExistance("songlanguage",$language);
		$state = 'publish';//0 'trash',1 'private',2 'pending',3 'draft',4 'future',5 'publish'
        $userId = wp_authenticate($username,$password);
        if(!$name)
        {
            $responseObj->status = "FAIL";
            $responseObj->message = "Musíte zadat název písně";
            $responseObj->code=-2;
        }
        else if(!is_wp_error($userId))
        {
            wp_set_current_user($userId);
            //Here comes the hard work:
            $songContent = HtmlToChordPro($songContent,true);
            $existingId = post_exists($name,'','','song');
            if($existingId != 0&&!$edit)
            {
                $responseObj->status = "EXISTS";
                $responseObj->message = "Píseň již existuje";
                $responseObj->postState = get_post_status($existingId);
                $responseObj->postID = $existingId;
            }
            else if($existingId != 0)
            {
                $result = editSongPost($existingId,$songContent,$author,$language,$state);
                if($result!=0&&!is_wp_error($result))
                {
                    $state = get_post_status($result);
                    $responseObj->postState = $state;
                    $responseObj->postID = $result;
                    $responseObj->url = getPostSlugByID($result);
                    $responseObj->edited = true;
                    if($state=='publish')
                    {
                        $responseObj->status = "OK";
                        $responseObj->message = "Píseň úspěšně změněna.";
                    }
                    else
                    {
                        $responseObj->status = "WAITING";
                        $responseObj->message = "Píseň úspěšně změněna. Čeká se na schválení editorem.";

                    }
                }
                else
                {
                    $responseObj->status = "FAIL";
                    $responseObj->code = is_wp_error($result)? $result->get_error_code():$result;
                    if(is_wp_error($result))
                        $responseObj->message = $result->get_error_message();
                }
            }
            else
            {
                $result = createSongPost($name,$songContent,$author,$language,$state);
                if($result!=0&&!is_wp_error($result))
                {
                    $state = get_post_status($result);
                    $responseObj->postState = $state;
                    $responseObj->postID = $result;
                    $responseObj->url = getPostSlugByID($result);
                    if($state=='publish')
                    {
                        $responseObj->status = "OK";
                        $responseObj->message = "Píseň úspěšně přidána.";

                    }
                    else
                    {
                        $responseObj->status = "WAITING";
                        $responseObj->message = "Píseň úspěšně přidána. Čeká se na schválení editorem.";
                    }
                }
                else
                {
                    $state = 'pending';//Pending approval
                    $result = createSongPost($name,$songContent,$author,$language,$state);
                    if($result==0||is_wp_error($result))
                    {
                        $responseObj->status = "FAIL";
                        $responseObj->code = is_wp_error($result)? $result->get_error_code():$result;
                        if(is_wp_error($result))
                            $responseObj->message = $result->get_error_message();
                    }
                    else
                    {
                        $responseObj->status = "WAITING";
                        $responseObj->message = "Píseň úspěšně přidána. Čeká se na schválení editorem.";
                        $responseObj->postState = $state;
                        $responseObj->postID = $result;
                        $responseObj->url = getPostSlugByID($result);
                    }
                }
            }
        }
        else
        {
            $responseObj->status = "FAIL";
            $responseObj->code = "-1"; //Invalid credentials
        }
    }
    else
    {
        $responseObj->status = "FAIL";
        $responseObj->code = "-1"; //Invalid credentials
    }

    $json = json_encode($responseObj);
    echo $json;
}
else
{
    if($_GET["exists"])
    {
        echo post_exists($_GET["exists"],'','','song');
        die;
    }
    echo "Bad method";
}
function getPostSlugByID($id)
{
    return get_post_field( 'post_name',$id);
}
function createSongPost($title,$body,$author,$language,$state)
{
    $authors = explode(',',$author);
    for($i=0; $i<sizeof($authors); $i++)
        $authors[$i] = trim($authors[$i]);

    $langs = explode(',',$language);
    for($i=0; $i<sizeof($langs); $i++)
        $langs[$i] = trim($langs[$i]);

    $content = array(
        'post_title' => $title,
        'post_content' => $body,
        'post_type' => 'song',
        'post_status' => $state
    );

    $p = wp_insert_post($content,true);
    if(strlen($author)>0)
        wp_set_object_terms( $p, $authors, 'songauthor' );
    if(strlen($language)>0)
        wp_set_object_terms( $p, $langs, 'songlanguage' );
    return $p;
}
function editSongPost($id,$body,$author,$language,$state)
{
    if(!current_user_can('edit_post', $id))
        return new WP_Error("-3","Uživatel nemůže upravovat píseň");
    $authors = explode(',',$author);
    for($i=0; $i<sizeof($authors); $i++)
        $authors[$i] = trim($authors[$i]);

    $langs = explode(',',$language);
    for($i=0; $i<sizeof($langs); $i++)
        $langs[$i] = trim($langs[$i]);

    $content = array(
        'ID' =>$id,
        'post_content' => $body,
        'post_status' => $state
    );
    $p = wp_update_post($content,true);
    if(strlen($author)>0)
        wp_set_object_terms( $p, $authors, 'songauthor' );
    if(strlen($language)>0)
        wp_set_object_terms( $p, $langs, 'songlanguage' );
    return $p;
}
?>