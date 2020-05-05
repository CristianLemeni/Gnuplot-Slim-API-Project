<?php
require 'vendor/autoload.php';
include 'config.php';

$app = new Slim\App(["settings" => $config]);
//Handle Dependencies
$container = $app->getContainer();

$container['db'] = function ($c) {
   
   try{
       $db = $c['settings']['db'];
       $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE                      => PDO::FETCH_ASSOC,
       );
       $pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'],
       $db['username'], $db['password'],$options);
       return $pdo;
   }
   catch(\Exception $ex){
       return $ex->getMessage();
   }
   
};

$app->post('/image', function ($request, $response) {
   
   try{
       $con = $this->db;
       $sql = "INSERT INTO `diagramtable`(`id`, `imgType`,`imgFunction`,`imgTitle`,`imgdX`,`imgdY`) VALUES (:id,:imgType,:imgFunction,:imgTitle,:imgdX,:imgdY)";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':id' => $request->getParam('id'),
       ':imgType' => $request->getParam('imgType'),
	   ':imgFunction' => $request->getParam('imgFunction'),
       ':imgTitle' => $request->getParam('imgTitle'),
	   ':imgdX' => $request->getParam('imgdX'),
       ':imgdY' => $request->getParam('imgdY')
       );
       $result = $pre->execute($values);
       return $response->withJson(array('status' => 'Image Created'),200);
       
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->get('/image/{id}', function ($request,$response) {
   try{
       $id     = $request->getAttribute('id');
       $con = $this->db;
       $sql = "SELECT * FROM diagramtable WHERE id = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':id' => $id);
       $pre->execute($values);
       $result = $pre->fetch();
       if($result){
           return $response->withJson(array('status' => 'true','result'=> $result),200);
       }else{
           return $response->withJson(array('status' => 'User Not Found'),422);
       }
      
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->get('/images', function ($request,$response) {
   try{
       $con = $this->db;
       $sql = "SELECT * FROM diagramtable";
       $result = null;
       foreach ($con->query($sql) as $row) {
           $result[] = $row;
       }
       if($result){
           return $response->withJson(array('status' => 'true','result'=>$result),200);
       }else{
           return $response->withJson(array('status' => 'Users Not Found'),422);
       }
              
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->put('/image/{id}', function ($request,$response) {
   try{
       $id  = $request->getAttribute('id');
       $con = $this->db;
       $sql = "UPDATE diagramtable SET id=:id,imgType=:imgType,imgFunction=:imgFunction,imgTitle=:imgTitle,imgdX=:imgdX,imgdY=:imgdY WHERE id = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':id' => $request->getParam('id'),
       ':imgType' => $request->getParam('imgType'),
	   ':imgFunction' => $request->getParam('imgFunction'),
       ':imgTitle' => $request->getParam('imgTitle'),
	   ':imgdX' => $request->getParam('imgdX'),
       ':imgdY' => $request->getParam('imgdY')
       );
       $result =  $pre->execute($values);
       if($result){
           return $response->withJson(array('status' => 'Image Updated'),200);
       }else{
           return $response->withJson(array('status' => 'Image Not Found'),422);
       }
              
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->delete('/image/{id}', function ($request,$response) {
   try{
       $id 	= $request->getAttribute('id');
       $con = $this->db;
       $sql = "DELETE FROM diagramtable WHERE id = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':id' => $id);
       $result = $pre->execute($values);
       if($result){
           return $response->withJson(array('status' => 'Image Deleted'),200);
       }else{
           return $response->withJson(array('status' => 'Image Not Found'),422);
       }
      
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

$app->run();