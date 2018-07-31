<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Entity\Appointment;

class AppointmentService extends AbstractController
{
    private $sql;

    public function __construct($sql) {
        $this->sql = $sql;
    }


    private function getConnection() {
        $connectionInfo = array(
            "UID" => $this->sql['user'],
            "PWD" => $this->sql['pw'],
            "Database" => $this->sql['db'],
            "CharacterSet" => $this->sql['charset']
        );

        $serverName = $this->sql['host'];
        $connection = sqlsrv_connect($serverName, $connectionInfo);

        if (!$connection) {
            return null;
        } 
        return $connection;
    }

    private function executeQuerySQLSRV($query) {
        $connection = $this->getConnection();
        $preparedStatement = sqlsrv_prepare($connection, $query);
        return sqlsrv_execute($preparedStatement);
    }

    private function getDataFromSQLSRV($query) {
        $connection = $this->getConnection();
        $preparedStatement = sqlsrv_prepare($connection, $query);
        sqlsrv_execute($preparedStatement);
        
        $results = array();
        while ($row = sqlsrv_fetch_array($preparedStatement, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    


    /**
     * @Route("/API/suppliers", name="suppliers")
     */
    public function getSuppliers()
    {
        $query = "exec [VGWebCais_Get_Fornecedor] ''";

        $results = $this->getDataFromSQLSRV($query);
        
        $suppliers = array();
        foreach($results as $supplier) {
            $suppliers[$supplier['forn_nome']] = $supplier['forn_counter'];
        }

        $json = json_encode($suppliers);

        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/API/suppliers/{supplierId}", name="supplier_by_id")
     */
    public function getSupplierById(string $supplierId)
    {
        $query = "exec [VGWebCais_Get_Fornecedor] 'subContrat = ".$supplierId."'";

        $results = $this->getDataFromSQLSRV($query);

        foreach($results as $supplier){
            if($supplier['forn_counter'] == $supplierId){
                $json = json_encode(array($supplier['forn_nome'] => $supplier['forn_counter']));

                $response = new Response($json, 200);
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }
        return null;
    }

    public function opToString($op, $opt = 'op') {
        
        if($opt == 'model'){
            $txt = '' . 
            sprintf("%-15s", $op['model']) .
            sprintf("%-15s", $op['id']) .
            sprintf("%-8s", $op['collection']) .
            sprintf("%-14s", 'QtdPrev: ' . round($op['qtd_prev']) ) . 
            sprintf("%-14s", 'QtdPend: ' . round($op['qtd_pend']) ) . 
            sprintf("%-14s", 'QtdEnt: ' . round($op['qtd_ent']) ) .
        '';
        } else {
            $txt = '' . 
            sprintf("%-15s", $op['id']) .
            sprintf("%-15s", $op['model']) .
            sprintf("%-8s", $op['collection']) .
            sprintf("%-14s", 'QtdPrev: ' . round($op['qtd_prev']) ) . 
            sprintf("%-14s", 'QtdPend: ' . round($op['qtd_pend']) ) . 
            sprintf("%-14s", 'QtdEnt: ' . round($op['qtd_ent']) ) .
        '';
        }

        //file_put_contents("/var/www/html/log.txt", "\n" . $txt, FILE_APPEND | LOCK_EX);

        return [
            $txt => $op['id']
        ];
    }

    private function opToArray($op) {
        if(empty($op)){
            return array();
        } 

        return [
            'id' => $op['op'],
            'depart' => $op['depart'],
            'model' => $op['ref'],
            'qtd_prev' => $op['qtd_prev'],
            'qtd_pend' => $op['qtd_pend'],
            'qtd_ent' => $op['qtd_ent'],
            'collection' => $op['estacao']
        ];
    }

    /**
     * @Route("/API/ops", name="ops")
     */
    public function getOps()
    {
        $query = "exec [VGWebCais_Get_OP] ''";

        $results = $this->getDataFromSQLSRV($query);
        
        $ops = array();
        foreach($results as $result){
            $ops[] = $this->opToArray($result);
        }
        $json = json_encode($ops);

        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/API/ops/supplier/{supplierId}", name="op_by_supplier_id")
     */
    public function getOpBySupplierId(
        string $supplierId
    ) {
        $query = "exec [VGWebCais_Get_OP] 'subContrat = ".$supplierId."'";

        $results = $this->getDataFromSQLSRV($query);

        $ops = array();
        foreach($results as $result){
            $ops[] = $this->opToArray($result);
        }

        if(!empty($ops)){
            $json = json_encode($ops);
            $response = new Response($json, 200);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        return new JsonResponse(array(
            'message' => 'message.InvalidSupplierId'
        ), 400);
    }


    /**
     * @Route("/API/ops/{id}", name="op_data_by_id")
     */
    public function getOpById(
        string $id
    ) {
        $query = "exec [VGWebCais_Get_OP] 'op.id = ''".$id."'' '";

        $results = $this->getDataFromSQLSRV($query);

        $ops = array();
        foreach($results as $result){
            $ops[] = $this->opToArray($result);
        }

        if(!empty($ops)){
            $json = json_encode($ops);

            $response = new Response($json, 200);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        return new JsonResponse(array(
            'message' => 'message.InvalidOpId'
        ), 400);
    }

    /**
     * @Route("/API/appointment/save", name="appointment_save")
     * @Method("POST")
     */
    public function saveAppointment(
        Appointment $entity
    ) {
        $query =    "INSERT INTO Cais_Marcacoes 
                        (id, duration, obs, supplier_id, supplier_name, 
                        start_date, end_date, doc_id, depart, doc_num, 
                        qtd, status_id, last_updated_user_id, last_updated_date) 
                    VALUES (
                        '" . $entity->getId() . "', 
                        '" . $entity->getDuration() . "', 
                        '" . $entity->getObs() . "', 
                        '" . $entity->getSupplierId() . "', 
                        '" . $entity->getSupplierName() . "', 
                        '" . $entity->getStartDate()->format('Y-m-d H:i:s') . "', 
                        '" . $entity->getEndDate()->format('Y-m-d H:i:s') . "', 
                        '" . $entity->getDocId() . "', 
                        '" . $entity->getDepart() . "', 
                        '" . $entity->getDocNum() . "', 
                        '" . $entity->getQtd() . "', 
                        '" . $entity->getStatus()->getId() . "', 
                        '" . $entity->getLastUpdatedUser()->getId() . "', 
                        '" . $entity->getLastUpdatedDate()->format('Y-m-d H:i:s') . "'
                    )";

        $this->executeQuerySQLSRV($query);
    }

    /**
     * @Route("/API/appointment/save/{id}", name="appointment_save_by_id")
     * @Method("POST")
     */
    public function updateAppointment(
        Appointment $entity
    ) {
        $query = "UPDATE Cais_Marcacoes SET status_id = '".$entity->getStatus()->getId()."' WHERE id = '".$entity->getId()."'";
        $this->executeQuerySQLSRV($query);
    }
   
}