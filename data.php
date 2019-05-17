<?php
namespace Cetera;
/************************************************************************************************

Список материалов

*************************************************************************************************/

try {
   
    include('common_bo.php');
    
    Material::clearLocks();
        
    $od = ObjectDefinition::findByAlias('comments');
        
    $math = $od->table;
    $type = $od->id;
        
    $where = '';
    
     $fields = array('name');
    
    if (!isset($_REQUEST['query'])) $_REQUEST['query'] = '';
    $query = '%'.addslashes($_REQUEST['query']).'%';
    
    $math_at_once = $_REQUEST['limit'];
    $m_first = $_REQUEST['start'];    
    
    $order = $_REQUEST['dir'];
    $sort = $_REQUEST['sort'];
    
    $sql = "SELECT SQL_CALC_FOUND_ROWS 
                   A.id, A.type, A.autor as autor_id, UNIX_TIMESTAMP(A.dat) as dat, 
                   IF(C.name<>'' and C.name IS NOT NULL, C.name, C.login) as autor, 
                   A.alias, D.user_id as locked, A.name, A.comment, A.material_id, A.material_type
            FROM $math A LEFT JOIN users C ON (A.autor=C.id) LEFT JOIN `lock` D ON (A.id = D.material_id and D.type_id=$type AND D.dat >= NOW()-INTERVAL 10 SECOND) 
            WHERE (A.name like '$query' or A.alias like '$query' or C.name like '$query' or C.login like '$query')
                  $where
            ORDER BY $sort $order
            LIMIT $m_first,$math_at_once";	
    $r  = $application->getConn()->fetchAll($sql);
    
    $all_filter = $application->getConn()->fetchColumn('SELECT FOUND_ROWS()',[],0);
    $i = 0;
        
    $materials = array();
    
    foreach ($r as $f) {
        
        $f['icon'] = ($f['type'] & MATH_PUBLISHED)?1:0;       
        $materials[] = $f;
        
    }
    
    echo json_encode(array(
        'success' => true,
        'total'   => $all_filter,
        'rows'    => $materials
    ));
    
} catch (Exception $e) {

    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'rows'    => false
    ));

}
