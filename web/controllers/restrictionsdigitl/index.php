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

$app->match('/restrictionsdigitl/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'P07', 
		'P09', 
		'P10', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
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
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `restrictionsdigitl`" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `restrictionsdigitl`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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
$app->match('/restrictionsdigitl/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . restrictionsdigitl . " WHERE ".$idfldname." = ?";
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



$app->match('/restrictionsdigitl', function () use ($app) {
    
	$table_columns = array(
		'SeqNR', 
		'P03', 
		'P07', 
		'P09', 
		'P10', 

    );

    $primary_key = "SeqNR";	

    return $app['twig']->render('restrictionsdigitl/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('restrictionsdigitl_list');



$app->match('/restrictionsdigitl/create', function () use ($app) {
    
    $initial_data = array(
		'SeqNR' => '', 
		'P03' => '', 
		'P07' => '', 
		'P09' => '', 
		'P10' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('SeqNR', 'text', array('required' => true));
	$form = $form->add('P03', 'text', array('required' => false));
	$form = $form->add('P07', 'text', array('required' => false));
	$form = $form->add('P09', 'text', array('required' => false));
	$form = $form->add('P10', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `restrictionsdigitl` (`SeqNR`, `P03`, `P07`, `P09`, `P10`) VALUES (?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['SeqNR'], $data['P03'], $data['P07'], $data['P09'], $data['P10']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'restrictionsdigitl created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('restrictionsdigitl_list'));

        }
    }

    return $app['twig']->render('restrictionsdigitl/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('restrictionsdigitl_create');



$app->match('/restrictionsdigitl/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `restrictionsdigitl` WHERE `SeqNR` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('restrictionsdigitl_list'));
    }

    
    $initial_data = array(
		'SeqNR' => $row_sql['SeqNR'], 
		'P03' => $row_sql['P03'], 
		'P07' => $row_sql['P07'], 
		'P09' => $row_sql['P09'], 
		'P10' => $row_sql['P10'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('SeqNR', 'text', array('required' => true));
	$form = $form->add('P03', 'text', array('required' => false));
	$form = $form->add('P07', 'text', array('required' => false));
	$form = $form->add('P09', 'text', array('required' => false));
	$form = $form->add('P10', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `restrictionsdigitl` SET `SeqNR` = ?, `P03` = ?, `P07` = ?, `P09` = ?, `P10` = ? WHERE `SeqNR` = ?";
            $app['db']->executeUpdate($update_query, array($data['SeqNR'], $data['P03'], $data['P07'], $data['P09'], $data['P10'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'restrictionsdigitl edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('restrictionsdigitl_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('restrictionsdigitl/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('restrictionsdigitl_edit');


$app->match('/restrictionsdigitl/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `restrictionsdigitl` WHERE `SeqNR` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `restrictionsdigitl` WHERE `SeqNR` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'restrictionsdigitl deleted!',
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

    return $app->redirect($app['url_generator']->generate('restrictionsdigitl_list'));

})
->bind('restrictionsdigitl_delete');



$app->match('/restrictionsdigitl/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'SeqNR', 
		'P03', 
		'P07', 
		'P09', 
		'P10', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
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
     
    $find_sql = "SELECT ".$columns_to_select." FROM `restrictionsdigitl`";
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
})->bind('restrictionsdigitl_downloadList');



