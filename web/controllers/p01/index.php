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

$app->match('/p01/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
		'P01', 
		'Brand', 
		'ProductSeries', 
		'Category', 
		'Circular', 
		'Rectangular', 
		'Miniature', 
		'PushPull', 
		'Fiber', 
		'WaterProof', 
		'Hermetic', 
		'HighSpeed', 
		'Standard', 
		'ULiEC', 
		'CircularBackShell', 
		'UL', 
		'iEC', 
		'CSA', 
		'MiL_DTL3899_I', 
		'HE308', 
		'M85049', 
		'EN3660', 
		'EN2997', 
		'ESC', 
		'Coupling', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
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
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `p01`" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `p01`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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
$app->match('/p01/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . p01 . " WHERE ".$idfldname." = ?";
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



$app->match('/p01', function () use ($app) {
    
	$table_columns = array(
		'SeqNR', 
		'P01', 
		'Brand', 
		'ProductSeries', 
		'Category', 
		'Circular', 
		'Rectangular', 
		'Miniature', 
		'PushPull', 
		'Fiber', 
		'WaterProof', 
		'Hermetic', 
		'HighSpeed', 
		'Standard', 
		'ULiEC', 
		'CircularBackShell', 
		'UL', 
		'iEC', 
		'CSA', 
		'MiL_DTL3899_I', 
		'HE308', 
		'M85049', 
		'EN3660', 
		'EN2997', 
		'ESC', 
		'Coupling', 

    );

    $primary_key = "SeqNR";	

    return $app['twig']->render('p01/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('p01_list');



$app->match('/p01/create', function () use ($app) {
    
    $initial_data = array(
		'SeqNR' => '', 
		'P01' => '', 
		'Brand' => '', 
		'ProductSeries' => '', 
		'Category' => '', 
		'Circular' => '', 
		'Rectangular' => '', 
		'Miniature' => '', 
		'PushPull' => '', 
		'Fiber' => '', 
		'WaterProof' => '', 
		'Hermetic' => '', 
		'HighSpeed' => '', 
		'Standard' => '', 
		'ULiEC' => '', 
		'CircularBackShell' => '', 
		'UL' => '', 
		'iEC' => '', 
		'CSA' => '', 
		'MiL_DTL3899_I' => '', 
		'HE308' => '', 
		'M85049' => '', 
		'EN3660' => '', 
		'EN2997' => '', 
		'ESC' => '', 
		'Coupling' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);



	$form = $form->add('SeqNR', 'text', array('required' => true));
	$form = $form->add('P01', 'text', array('required' => false));
	$form = $form->add('Brand', 'text', array('required' => false));
	$form = $form->add('ProductSeries', 'text', array('required' => false));
	$form = $form->add('Category', 'text', array('required' => false));
	$form = $form->add('Circular', 'text', array('required' => false));
	$form = $form->add('Rectangular', 'text', array('required' => false));
	$form = $form->add('Miniature', 'text', array('required' => false));
	$form = $form->add('PushPull', 'text', array('required' => false));
	$form = $form->add('Fiber', 'text', array('required' => false));
	$form = $form->add('WaterProof', 'text', array('required' => false));
	$form = $form->add('Hermetic', 'text', array('required' => false));
	$form = $form->add('HighSpeed', 'text', array('required' => false));
	$form = $form->add('Standard', 'text', array('required' => false));
	$form = $form->add('ULiEC', 'text', array('required' => false));
	$form = $form->add('CircularBackShell', 'text', array('required' => false));
	$form = $form->add('UL', 'text', array('required' => false));
	$form = $form->add('iEC', 'text', array('required' => false));
	$form = $form->add('CSA', 'text', array('required' => false));
	$form = $form->add('MiL_DTL3899_I', 'text', array('required' => false));
	$form = $form->add('HE308', 'text', array('required' => false));
	$form = $form->add('M85049', 'text', array('required' => false));
	$form = $form->add('EN3660', 'text', array('required' => false));
	$form = $form->add('EN2997', 'text', array('required' => false));
	$form = $form->add('ESC', 'text', array('required' => false));
	$form = $form->add('Coupling', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `p01` (`SeqNR`, `P01`, `Brand`, `ProductSeries`, `Category`, `Circular`, `Rectangular`, `Miniature`, `PushPull`, `Fiber`, `WaterProof`, `Hermetic`, `HighSpeed`, `Standard`, `ULiEC`, `CircularBackShell`, `UL`, `iEC`, `CSA`, `MiL_DTL3899_I`, `HE308`, `M85049`, `EN3660`, `EN2997`, `ESC`, `Coupling`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['SeqNR'], $data['P01'], $data['Brand'], $data['ProductSeries'], $data['Category'], $data['Circular'], $data['Rectangular'], $data['Miniature'], $data['PushPull'], $data['Fiber'], $data['WaterProof'], $data['Hermetic'], $data['HighSpeed'], $data['Standard'], $data['ULiEC'], $data['CircularBackShell'], $data['UL'], $data['iEC'], $data['CSA'], $data['MiL_DTL3899_I'], $data['HE308'], $data['M85049'], $data['EN3660'], $data['EN2997'], $data['ESC'], $data['Coupling']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'p01 created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('p01_list'));

        }
    }

    return $app['twig']->render('p01/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('p01_create');



$app->match('/p01/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `p01` WHERE `SeqNR` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('p01_list'));
    }

    
    $initial_data = array(
		'SeqNR' => $row_sql['SeqNR'], 
		'P01' => $row_sql['P01'], 
		'Brand' => $row_sql['Brand'], 
		'ProductSeries' => $row_sql['ProductSeries'], 
		'Category' => $row_sql['Category'], 
		'Circular' => $row_sql['Circular'], 
		'Rectangular' => $row_sql['Rectangular'], 
		'Miniature' => $row_sql['Miniature'], 
		'PushPull' => $row_sql['PushPull'], 
		'Fiber' => $row_sql['Fiber'], 
		'WaterProof' => $row_sql['WaterProof'], 
		'Hermetic' => $row_sql['Hermetic'], 
		'HighSpeed' => $row_sql['HighSpeed'], 
		'Standard' => $row_sql['Standard'], 
		'ULiEC' => $row_sql['ULiEC'], 
		'CircularBackShell' => $row_sql['CircularBackShell'], 
		'UL' => $row_sql['UL'], 
		'iEC' => $row_sql['iEC'], 
		'CSA' => $row_sql['CSA'], 
		'MiL_DTL3899_I' => $row_sql['MiL_DTL3899_I'], 
		'HE308' => $row_sql['HE308'], 
		'M85049' => $row_sql['M85049'], 
		'EN3660' => $row_sql['EN3660'], 
		'EN2997' => $row_sql['EN2997'], 
		'ESC' => $row_sql['ESC'], 
		'Coupling' => $row_sql['Coupling'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('SeqNR', 'text', array('required' => true));
	$form = $form->add('P01', 'text', array('required' => false));
	$form = $form->add('Brand', 'text', array('required' => false));
	$form = $form->add('ProductSeries', 'text', array('required' => false));
	$form = $form->add('Category', 'text', array('required' => false));
	$form = $form->add('Circular', 'text', array('required' => false));
	$form = $form->add('Rectangular', 'text', array('required' => false));
	$form = $form->add('Miniature', 'text', array('required' => false));
	$form = $form->add('PushPull', 'text', array('required' => false));
	$form = $form->add('Fiber', 'text', array('required' => false));
	$form = $form->add('WaterProof', 'text', array('required' => false));
	$form = $form->add('Hermetic', 'text', array('required' => false));
	$form = $form->add('HighSpeed', 'text', array('required' => false));
	$form = $form->add('Standard', 'text', array('required' => false));
	$form = $form->add('ULiEC', 'text', array('required' => false));
	$form = $form->add('CircularBackShell', 'text', array('required' => false));
	$form = $form->add('UL', 'text', array('required' => false));
	$form = $form->add('iEC', 'text', array('required' => false));
	$form = $form->add('CSA', 'text', array('required' => false));
	$form = $form->add('MiL_DTL3899_I', 'text', array('required' => false));
	$form = $form->add('HE308', 'text', array('required' => false));
	$form = $form->add('M85049', 'text', array('required' => false));
	$form = $form->add('EN3660', 'text', array('required' => false));
	$form = $form->add('EN2997', 'text', array('required' => false));
	$form = $form->add('ESC', 'text', array('required' => false));
	$form = $form->add('Coupling', 'text', array('required' => false));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `p01` SET `SeqNR` = ?, `P01` = ?, `Brand` = ?, `ProductSeries` = ?, `Category` = ?, `Circular` = ?, `Rectangular` = ?, `Miniature` = ?, `PushPull` = ?, `Fiber` = ?, `WaterProof` = ?, `Hermetic` = ?, `HighSpeed` = ?, `Standard` = ?, `ULiEC` = ?, `CircularBackShell` = ?, `UL` = ?, `iEC` = ?, `CSA` = ?, `MiL_DTL3899_I` = ?, `HE308` = ?, `M85049` = ?, `EN3660` = ?, `EN2997` = ?, `ESC` = ?, `Coupling` = ? WHERE `SeqNR` = ?";
            $app['db']->executeUpdate($update_query, array($data['SeqNR'], $data['P01'], $data['Brand'], $data['ProductSeries'], $data['Category'], $data['Circular'], $data['Rectangular'], $data['Miniature'], $data['PushPull'], $data['Fiber'], $data['WaterProof'], $data['Hermetic'], $data['HighSpeed'], $data['Standard'], $data['ULiEC'], $data['CircularBackShell'], $data['UL'], $data['iEC'], $data['CSA'], $data['MiL_DTL3899_I'], $data['HE308'], $data['M85049'], $data['EN3660'], $data['EN2997'], $data['ESC'], $data['Coupling'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'p01 edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('p01_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('p01/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('p01_edit');


$app->match('/p01/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `p01` WHERE `SeqNR` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `p01` WHERE `SeqNR` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'p01 deleted!',
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

    return $app->redirect($app['url_generator']->generate('p01_list'));

})
->bind('p01_delete');



$app->match('/p01/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'SeqNR', 
		'P01', 
		'Brand', 
		'ProductSeries', 
		'Category', 
		'Circular', 
		'Rectangular', 
		'Miniature', 
		'PushPull', 
		'Fiber', 
		'WaterProof', 
		'Hermetic', 
		'HighSpeed', 
		'Standard', 
		'ULiEC', 
		'CircularBackShell', 
		'UL', 
		'iEC', 
		'CSA', 
		'MiL_DTL3899_I', 
		'HE308', 
		'M85049', 
		'EN3660', 
		'EN2997', 
		'ESC', 
		'Coupling', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
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
     
    $find_sql = "SELECT ".$columns_to_select." FROM `p01`";
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
})->bind('p01_downloadList');



