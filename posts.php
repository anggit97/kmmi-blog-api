<?php
require_once "koneksi.php";

$base_url = "http://localhost:8888/blog-api/uploads/";

if(function_exists($_GET['function'])){
    $_GET['function']();
}

function get_posts(){
    global $connect;
    global $base_url;
    $query = $connect->query("SELECT * FROM posts ORDER BY id DESC");
    $data = array();
    while($row=mysqli_fetch_object($query)){
        $data[] = $row;
    }

    $i = 0;
    foreach($data as $dataItem){
        $data[$i]->image_path = $base_url.$data[$i]->image_path;
        $i++;
    }

    $response = array(
        'status' => 1,
        'message' => 'Success',
        'data' => $data
    );
    header("Content-Type: application/json");
    echo json_encode($response);
}

function get_posts_by_id(){
    global $connect;
    global $base_url;
    if(!empty($_GET["id"])){
        $id = $_GET["id"];
    }
    $query = "SELECT * FROM posts WHERE id = $id";
    $result = $connect->query($query);
    while($row = mysqli_fetch_object($result)){
        $data[] = $row;
    }

    $i = 0;
    foreach($data as $dataItem){
        $data[$i]->image_path = $base_url.$data[$i]->image_path;
        $i++;
    }

    if($data){
        $response = array(
            'status' => 1,
            'message' => 'Success',
            'data' => $data
        );
    }else{
        $response = array(
            'status' => 0,
            'message' => 'Data not found'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function insert_posts(){
    global $connect;

    $data = file_get_contents("php://input");
    $decoded_data = json_decode($data);
    
    $check = array('title' => '', 'body' => '', 'image_path' => '');

    $check_match = count(array_intersect_key((array)$decoded_data, $check));
    if($check_match == count($check)){
        $now = date("Y-m-d H:i:s");
        
        $target_dir = 'uploads/'; // add the specific path to save the file
        $decoded_file = base64_decode($decoded_data->image_path); // decode the file
        $mime_type = finfo_buffer(finfo_open(), $decoded_file, FILEINFO_MIME_TYPE); // extract mime type
        $extension = mime2ext($mime_type); // extract extension from mime type
        $file = uniqid() .'.'. $extension; // rename file as a unique name
        $file_dir = $target_dir . $file;
        file_put_contents($file_dir, $decoded_file); // save
        
        $result = mysqli_query($connect, "INSERT INTO posts SET 
        title = '$decoded_data->title',
        body = '$decoded_data->body',
        image_path = '$file',
        created_at = '$now',
        updated_at = '$now'");

        if($result){
            $response=array(
                'status' => 1,
                'message' =>'Insert Success'
             );
        }else{
            $response=array(
                'status' => 0,
                'message' =>'Insert Failed.'
             );
        }
    }else{
        $response=array(
            'status' => 0,
            'message' =>'Wrong Parameter'
         );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

function update_posts(){
    global $connect;
    
    if(!empty($_GET['id'])){
        $id = $_GET['id'];
    }

    $data = file_get_contents("php://input");
    $decoded_data = json_decode($data);

    $check = array('title' => '', 'body' => '', 'image_path' => '');
    $check_match = count(array_intersect_key((array)$decoded_data, $check));
    if($check_match == count($check)){
        $now = date("Y-m-d H:i:s");
        $result = mysqli_query($connect, "UPDATE posts SET 
        title = '$decoded_data->title',
        body = '$decoded_data->body',
        image_path = 'test.png',
        updated_at = '$now'
        WHERE id = '$id'");

        if($result){
            $response=array(
                'status' => 1,
                'message' =>'Update Success'
            );
        }else{
            $response=array(
                'status' => 0,
                'message' =>'Update Failed.'
            );
        }
    }else{
        $response=array(
            'status' => 0,
            'message' =>'Wrong Parameter'
         );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

function delete_posts(){
    global $connect;

    if(!empty($_GET['id'])){
        $id = $_GET['id'];
    }

    $query = "DELETE FROM posts WHERE id=".$id;
    if(mysqli_query($connect, $query))
    {
        $response=array(
        'status' => 1,
        'message' =>'Delete Success'
        );
    }
    else
    {
        $response=array(
        'status' => 0,
        'message' =>'Delete Fail.'
        );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

/*
to take mime type as a parameter and return the equivalent extension
*/
function mime2ext($mime){
    $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp",
    "image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp",
    "image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp",
    "application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg",
    "image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],
    "wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],
    "ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg",
    "video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],
    "kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],
    "rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application",
    "application\/x-jar"],"zip":["application\/x-zip","application\/zip",
    "application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],
    "7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],
    "svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],
    "mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],
    "webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],
    "pdf":["application\/pdf","application\/octet-stream"],
    "pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],
    "ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office",
    "application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],
    "xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],
    "xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel",
    "application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],
    "xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo",
    "video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],
    "log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],
    "wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],
    "tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop",
    "image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],
    "mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar",
    "application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40",
    "application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],
    "cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary",
    "application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],
    "ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],
    "wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],
    "dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php",
    "application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],
    "swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],
    "mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],
    "rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],
    "jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],
    "eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],
    "p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],
    "p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
    $all_mimes = json_decode($all_mimes,true);
    foreach ($all_mimes as $key => $value) {
        if(array_search($mime,$value) !== false) return $key;
    }
    return false;
}
