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

    //GNUPLOT
    $gnuplotFile = fopen("D:\gnuplot\client\gnuplotFile.p", "w");
    $imgType = $request->getParam('imgType');
    $imgFunction = $request->getParam('imgFunction');
    $imgTitle = $request->getParam('imgTitle');
    $imgdX = $request->getParam('imgdX');
    $imgdY = $request->getParam('imgdY');

    //set output 
    $setOutputImg = "set terminal ".$imgType." size 500,500"."\n";
    fwrite($gnuplotFile, $setOutputImg);
    //set output file
    $setOutputFile = "set output 'D:\gnuplot\client\gnuplot.png'"."\n";
    fwrite($gnuplotFile, $setOutputFile);
    //set output title
    $setOutputTitle = "set title 'Gnuplot Graphic'"."\n";
    fwrite($gnuplotFile, $setOutputTitle);
    //plot graphic
    $setOutputPlot = "plot".$imgdX.$imgdY." ".$imgFunction."\n";
    fwrite($gnuplotFile, $setOutputPlot);

    fclose($gnuplotFile);


    exec("D:\gnuplot\client\gnuplot\bin\gnuplot.exe D:\gnuplot\client\gnuplotFile.p"); 
   
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

       //GNUPLOT
        $gnuplotFile = fopen("D:\gnuplot\client\gnuplotFile.p", "w");
        $imgType = $result['imgType'];
        $imgFunction = $result['imgFunction'];
        $imgTitle = $result['imgTitle'];
        $imgdX = $result['imgdX'];
        $imgdY = $result['imgdY'];

        //set output 
        $setOutputImg = "set terminal ".$imgType." size 500,500"."\n";
        fwrite($gnuplotFile, $setOutputImg);
        //set output file
        $setOutputFile = "set output 'D:\gnuplot\client\gnuplot.png'"."\n";
        fwrite($gnuplotFile, $setOutputFile);
        //set output title
        $setOutputTitle = "set title 'Gnuplot Graphic'"."\n";
        fwrite($gnuplotFile, $setOutputTitle);
        //plot graphic
        $setOutputPlot = "plot".$imgdX.$imgdY." ".$imgFunction."\n";
        fwrite($gnuplotFile, $setOutputPlot);

        fclose($gnuplotFile);


        exec("D:\gnuplot\client\gnuplot\bin\gnuplot.exe D:\gnuplot\client\gnuplotFile.p");

       if($result){
           return $response->withJson(array('status' => 'true','result'=> $result),200);
       }else{
           return $response->withJson(array('status' => 'Image Not Found'),422);
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

       $count = 0;
       foreach ($con->query($sql) as $row) {
           $result[] = $row;

            $gnuplotFile = fopen("D:\gnuplot\client\gnuplotFile.p", "w");
            $imgType =  $row['imgType'];
            $imgFunction = $row['imgFunction'];
            $imgTitle = $row['imgTitle'];
            $imgdX = $row['imgdX'];
            $imgdY = $row['imgdY'];
            $name = $count."gnuplot.".$imgType;
            //set output 
            $setOutputImg = "set terminal ".$imgType." size 500,500"."\n";
            fwrite($gnuplotFile, $setOutputImg);
            //set output file
            $setOutputFile = "set output 'D:/gnuplot/client/$name'"."\n";
            fwrite($gnuplotFile, $setOutputFile);
            //set output title
            $setOutputTitle = "set title 'Gnuplot Graphic'"."\n";
            fwrite($gnuplotFile, $setOutputTitle);
            //plot graphic
            $setOutputPlot = "plot".$imgdX.$imgdY." ".$imgFunction."\n";
            fwrite($gnuplotFile, $setOutputPlot);

            fclose($gnuplotFile);
                    

            exec("D:\gnuplot\client\gnuplot\bin\gnuplot.exe D:\gnuplot\client\gnuplotFile.p");
            $count++;
       }
       if($result){
           return $response->withJson(array('status' => 'true','result'=>$result, $count),200);
       }else{
           return $response->withJson(array('status' => 'Images Not Found'),422);
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