<?php
function print_response($dictionary = [], $error = "none"){
    $string = "";

    #convert our dictionary into a JSON string:
    $string = "{\"error\" : \"$error\",
                \"command\" : \"$_REQUEST[command]\",
                \"response\" : ". json_encode($dictionary)."}";
    
    #Print out our json to Godot!
    echo $string;
}

if(!isset($_REQUEST['command'])or $_REQUEST['command'] === null){
    #print_response([], "missing_command");
    echo "{\"error\" : \"missing_command\", \"response\" : {}}";
    die;
}

if(!isset($_REQUEST['data'])or $_REQUEST['data'] === null){
    print_response([], "missing_data");
    die;
}

$sql_host = "localhost";
$sql_db = "MS_DB";
$aql_username = "root";
$sql_password = "";

$dsn = "mysql:dbname=$sql_db;host=$sql_host;charset=utf8mb4;port=3306";
$pdo = null;

try{
    $pdo = new PDO($dsn, $sql_username, $sql_password);
}

catch (\PDOException $e){
    print_response([], "db_login_error");
    die;
}

$json = json_decode($_REQUEST['data'], true);

if ($json === null){
    print_response([], "invalid_json");
    die;
}

switch ($_REQUEST['command']){
    case "get_scores":
        $score_offset = 0;
        $score_number = 10;

        if(isset($json['score_offset']))
            $score_start = max(0, (int) $json['score_offset']);

        if(isset($json['score_number']))
            $score_number = max(1, (int) $json['score_number']);
        
        #form ql req template
        $template = "SELECT * FROM `highscores` ORDER BY score DESC LIMIT :number OFFSET :offset";
        
        #prepare and send
        $sth = $pdo -> prepare($template);
        $sth -> execute(["number" => $score_number, "offset" => $score_offset]);

        $data = $sth -> fetchAll();

        $data["size"] = sizeof($data);
        print_response($data);

    case "add_score":
        if (!isset($json['score'])){
            print_response([], "missing_score");
            die;
        }

        if (!isset($json['username'])){
            print_response([], "missing_score");
            die;
        }
        
        $username = $json['username'];
        if (strlen($username)>24)
        $username = substr($username, 24);
        
        #form our sql request string
        $template = "INSERT INTO `highscores` (username, score) VALUES (:username, :score) ON DUPLICATE KEY UPDATE score = GREATEST(score, VALUES(score))";

        #Print and send the request to the DB:
        $sth = $pdo -> prepare($template);
        $sth -> execute(["username" => $username, "score" => $json['score']]);
        
        print_response();
        die;
    break;

    default:
        print_response([], "invalid_command");
        die;
    break;
}








?>