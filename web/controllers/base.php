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


require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../../src/app.php';


require_once __DIR__.'/p01/index.php';
require_once __DIR__.'/p02/index.php';
require_once __DIR__.'/p03/index.php';
require_once __DIR__.'/p04/index.php';
require_once __DIR__.'/p05/index.php';
require_once __DIR__.'/p06/index.php';
require_once __DIR__.'/p07/index.php';
require_once __DIR__.'/p08/index.php';
require_once __DIR__.'/p09/index.php';
require_once __DIR__.'/p10/index.php';
require_once __DIR__.'/p11/index.php';
require_once __DIR__.'/restrictionscontactpicot/index.php';
require_once __DIR__.'/restrictionsdigitl/index.php';
require_once __DIR__.'/restrictionslayout/index.php';
require_once __DIR__.'/restrictionsnmancontact/index.php';
require_once __DIR__.'/restrictionsprotectcorps/index.php';
require_once __DIR__.'/restrictionstaillepicot/index.php';



$app->match('/', function () use ($app) {

    return $app['twig']->render('ag_dashboard.html.twig', array());
        
})
->bind('dashboard');


$app->run();