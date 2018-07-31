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
use App\Entity\User;

class UserService extends AbstractController
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
     * @Route("/API/user", name="api_get_users")
     */
    public function getUsers()
    {
        $query = "select utilizador, nome from utilizadores";

        $results = $this->getDataFromSQLSRV($query);
        
        $users = array();
        foreach($results as $user) {
            $users[$user['nome']] = $user['utilizador'];
        }

        $json = json_encode($users);

        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/API/user/{id}", name="api_get_user_data")
     */
    public function getUserData(string $id)
    {
        $query = "select utilizador, nome, password, empreg from utilizadores where utilizador = '$id'";

        $results = $this->getDataFromSQLSRV($query);

        $data = array();
        foreach($results as $result){
            $data[] = $this->userToArray($result);
        }

        if(!empty($data)){
            $json = json_encode($data);

            $response = new Response($json, 200);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        
        return new JsonResponse(array(
            'message' => 'message.InvalidOpId'
        ), 400);
    }


    private function userToArray($user) {
        if(empty($user)){
            return array();
        } 

        return [
            'utilizador' => $user['utilizador'],
            'nome' => $user['nome'],
            'password' => $user['password'],
            'empreg' => $user['empreg']
        ];
    }






}