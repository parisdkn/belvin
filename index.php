<?php
/**
 * Created by Paris on 21-01-16.
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;

/*
exemple de route slim 
$app->get('/', function (Request $request, Response $response) { 
    $response->getBody()->write("{'nom':'Durand','age':'28'}");

    return $response;
});*/

$app->get('/api/wines', function (Request $request, Response $response) {
    try {
        $db = thisConnection();
        $stmt = $db->query("SELECT* FROM wine ORDER BY name");
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        $response->getBody()->write('{"vins": ' . json_encode($wines) . '}');
    } catch(PDOException $e) {
        $response->getBody()->write('{"error":'. $e->getMessage() .'}');
        die();
    }
    return $response;
});

$app->get('/api/wines/{id}', function (Request $request, Response $response, $args) {
    try {
	$id = $args['id'];
        $db = thisConnection();
        $stmt = $db->query("SELECT* FROM wine WHERE id=".$id." ORDER BY name");
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        $response->getBody()->write('{"vinsId": ' . json_encode($wines) . '}');
    } catch(PDOException $e) {
        $response->getBody()->write('{"error":'. $e->getMessage() .'}');
        die();
    }
    return $response;
});

$app->get('/api/wines/search/{keyword}', function (Request $request, Response $response, $args) {

    try {
        $db = thisConnection();
        $keyword = $db->quote("%".$args['keyword']."%");
        $stmt = $db->query("SELECT* FROM wine WHERE name LIKE $keyword ORDER BY name");
        $wines = $stmt->fetchAll(PDO::FETCH_OBJ);
        $response->getBody()->write('{"VinsKeyword": ' . json_encode($wines) . '}');
    } catch(PDOException $e) {
        $response->getBody()->write('{"error":'. $e->getMessage() .'}');
        die();
    }
    return $response;
});

/**
*TO DO
*tester et checker les erreurs resultantes
*verifier  et securiser les données
**/
$app->post('/api/add/wines', function (Request $request, Response $response) {
	$request = $app->request();   //   or $app = \Slim\Slim::getInstance();
	$body = $request->getBody();  //      $allPostVars = $app->request->post(); renvoie null si non trouvé
    $input = json_decode($body);  //      $name = $allPostVars['name'];
    $sql = "INSERT INTO wine(name,grapes,country,region,year,description) VALUES(:name, :grapes, :country, :region, :year, :description)";
    try {
        $db = thisConnection();
        $stmt = $db->prepare($sql) or exit(print_r($db->errorInfo()));
	$stmt->bindParam("name", $input->name);  //remplacer $input->name par $name etc.
	$stmt->bindParam("grapes", $input->grapes);
	$stmt->bindParam("country", $input->country);
	$stmt->bindParam("region", $input->region);
	$stmt->bindParam("year", $input->year);
	$stmt->bindParam("description", $input->description);
        $stmt->execute();
        $input->id = $db->lastInsertId(); 
        $response->getBody()->write(json_encode($input));
    } catch(PDOException $e) {
        $response->getBody()->write('{"error":'. $e->getMessage() .'}');
        die();
    }
    return $response;
});

$app->put('/api/wines/{id}', function (Request $request, Response $response, $args) {
	$request = $app->request();
    $body = $request->getBody();   // or $allPutVars = $app->request->put();
    $input = json_decode($body);
    $sql = "UPDATE wine SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description WHERE id=:id";
	$id = $args['id'];
    try {
        $db = thisConnection();
        $stmt = $db->prepare($sql);
	$stmt->bindParam("name", $input->name);
	$stmt->bindParam("grapes", $input->grapes);
	$stmt->bindParam("country", $input->country);
	$stmt->bindParam("region", $input->region);
	$stmt->bindParam("year", $input->year);
	$stmt->bindParam("description", $input->description);
	$stmt->bindParam("id", $id);
        $stmt->execute();
        $response->getBody()->write(json_encode($input));
    } catch(PDOException $e) {
        $response->getBody()->write('{"error":'. $e->getMessage() .'}');
        die();
    }
    return $response;
});

$app->delete('/api/wines/{id}', function (Request $request, Response $response, $args) {
	$id = $args['id'];
    try {
        $db = thisConnection();
        $stmt = $db->prepare("DELETE FROM wine WHERE id=:id");
	$stmt->bindParam("id", $id);
        $lignes = $stmt->execute();
	$response->getBody()->write($lignes.' ligne(s) supprimée(s)');
    } catch(PDOException $e) {
        $response->getBody()->write('{"error":'. $e->getMessage() .'}');
        die();
    }
    return $response;
	
});

$app->run();

function thisConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbmdp="";
    $dbname="cavavin";
    $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbmdp);

    return $connection;
}
