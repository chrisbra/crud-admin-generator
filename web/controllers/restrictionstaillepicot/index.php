<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';
require_once __DIR__.'/../../../src/utils.php';


use Symfony\Component\Validator\Constraints as Assert;

$app->match('/restrictionstaillepicot/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->request->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'SeqNR', 
		'P03', 
		'TailleCT', 
		'Gender', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `restrictionstaillepicot`" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `restrictionstaillepicot`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		if( $table_columns_type[$i] != "blob") {
				$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
		} else {				if( !$row_sql[$table_columns[$i]] ) {
						$rows[$row_key][$table_columns[$i]] = "0 Kb.";
				} else {
						$rows[$row_key][$table_columns[$i]] = " <a target='__blank' href='menu/download?id=" . $row_sql[$table_columns[0]];
						$rows[$row_key][$table_columns[$i]] .= "&fldname=" . $table_columns[$i];
						$rows[$row_key][$table_columns[$i]] .= "&idfld=" . $table_columns[0];
						$rows[$row_key][$table_columns[$i]] .= "'>";
						$rows[$row_key][$table_columns[$i]] .= number_format(strlen($row_sql[$table_columns[$i]]) / 1024, 2) . " Kb.";
						$rows[$row_key][$table_columns[$i]] .= "</a>";
				}
		}

        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});




/* Download blob img */
$app->match('/restrictionstaillepicot/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . restrictionstaillepicot . " WHERE ".$idfldname." = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($rowid));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('menu_list'));
    }

    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header("Content-length: ".strlen( $row_sql[$fieldname] ));
    header('Expires: 0');
    header('Cache-Control: public');
    header('Pragma: public');
    ob_clean();    
    echo $row_sql[$fieldname];
    exit();
   
    
});



$app->match('/restrictionstaillepicot', function () use ($app) {
    
	$table_columns = array(
		'SeqNR', 
		'P03', 
		'TailleCT', 
		'Gender', 

    );

    $primary_key = "SeqNR";	

    return $app['twig']->render('restrictionstaillepicot/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('restrictionstaillepicot_list');



$app->match('/restrictionstaillepicot/create', function () use ($app) {
    
    $initial_data = array(
		'SeqNR' => '', 
		'P03' => '', 
		'TailleCT' => '', 
		'Gender' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('SeqNR', 'text', array('required' => true));
	$form = $form->add('P03', 'text', array('required' => false));
	$form = $form->add('TailleCT', 'text', array('required' => false));
	$form = $form->add('Gender', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `restrictionstaillepicot` (`SeqNR`, `P03`, `TailleCT`, `Gender`) VALUES (?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['SeqNR'], $data['P03'], $data['TailleCT'], $data['Gender']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'restrictionstaillepicot created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('restrictionstaillepicot_list'));

        }
    }

    return $app['twig']->render('restrictionstaillepicot/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('restrictionstaillepicot_create');



$app->match('/restrictionstaillepicot/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `restrictionstaillepicot` WHERE `SeqNR` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('restrictionstaillepicot_list'));
    }

    
    $initial_data = array(
		'SeqNR' => $row_sql['SeqNR'], 
		'P03' => $row_sql['P03'], 
		'TailleCT' => $row_sql['TailleCT'], 
		'Gender' => $row_sql['Gender'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('SeqNR', 'text', array('required' => true));
	$form = $form->add('P03', 'text', array('required' => false));
	$form = $form->add('TailleCT', 'text', array('required' => false));
	$form = $form->add('Gender', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `restrictionstaillepicot` SET `SeqNR` = ?, `P03` = ?, `TailleCT` = ?, `Gender` = ? WHERE `SeqNR` = ?";
            $app['db']->executeUpdate($update_query, array($data['SeqNR'], $data['P03'], $data['TailleCT'], $data['Gender'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'restrictionstaillepicot edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('restrictionstaillepicot_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('restrictionstaillepicot/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('restrictionstaillepicot_edit');


$app->match('/restrictionstaillepicot/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `restrictionstaillepicot` WHERE `SeqNR` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `restrictionstaillepicot` WHERE `SeqNR` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'restrictionstaillepicot deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('restrictionstaillepicot_list'));

})
->bind('restrictionstaillepicot_delete');



$app->match('/restrictionstaillepicot/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'SeqNR', 
		'P03', 
		'TailleCT', 
		'Gender', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 

    );   

    $types_to_cut = array('blob');
    $index_of_types_to_cut = array();
    foreach ($table_columns_type as $key => $value) {
        if(in_array($value, $types_to_cut)){
            unset($table_columns[$key]);
        }
    }

    $columns_to_select = implode(',', array_map(function ($row){
        return '`'.$row.'`';
    }, $table_columns));
     
    $find_sql = "SELECT ".$columns_to_select." FROM `restrictionstaillepicot`";
    $rows_sql = $app['db']->fetchAll($find_sql, array());
  
    $mpdf = new mPDF();

    $stylesheet = file_get_contents('../web/resources/css/bootstrap.min.css'); // external css
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML('.table {
    border-radius: 5px;
    width: 100%;
    margin: 0px auto;
    float: none;
}',1);

    $mpdf->WriteHTML(build_table($rows_sql));
    $mpdf->Output();
})->bind('restrictionstaillepicot_downloadList');



