<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AppointmentRepository")
 * @UniqueEntity("id")
 */
class Appointment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doc_id;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $depart;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doc_num;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start_date;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $end_date;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $duration;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $obs;

    /**
     * @ORM\Column(type="integer")
     */
    private $qtd;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $supplier_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $supplier_name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AppointmentStatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_updated_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $last_updated_user;

    
    
    
    // getters & setters 
    
    public function getId() {
        return $this->id;
    }

    /**
     * Get the value of start_date
     */ 
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set the value of start_date
     *
     * @return self
     */ 
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;

        return $this;
    }

    /**
     * Get the value of end_date
     */ 
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Set the value of end_date
     *
     * @return self
     */ 
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Get the value of duration
     */ 
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set the value of duration
     *
     * @return self
     */ 
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get the value of obs
     */ 
    public function getObs()
    {
        return $this->obs;
    }

    /**
     * Set the value of obs
     *
     * @return self
     */ 
    public function setObs($obs)
    {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get the value of supplier_id
     */ 
    public function getSupplierId()
    {
        return $this->supplier_id;
    }

    /**
     * Set the value of supplier_id
     *
     * @return self
     */ 
    public function setSupplierId($supplier_id)
    {
        $this->supplier_id = $supplier_id;

        return $this;
    }

    /**
     * Get the value of supplier_name
     */ 
    public function getSupplierName()
    {
        return $this->supplier_name;
    }

    /**
     * Set the value of supplier_name
     *
     * @return self
     */ 
    public function setSupplierName($supplier_name)
    {
        $this->supplier_name = $supplier_name;

        return $this;
    }

    /**
     * Get the value of doc_id
     */ 
    public function getDocId()
    {
        return $this->doc_id;
    }

    /**
     * Set the value of doc_id
     *
     * @return self
     */ 
    public function setDocId($doc_id)
    {
        $this->doc_id = $doc_id;

        return $this;
    }

    /**
     * Get the value of depart
     */ 
    public function getDepart()
    {
        return $this->depart;
    }

    /**
     * Set the value of depart
     *
     * @return self
     */ 
    public function setDepart($depart)
    {
        $this->depart = $depart;

        return $this;
    }

    /**
     * Get the value of doc_num
     */ 
    public function getDocNum()
    {
        return $this->doc_num;
    }

    /**
     * Set the value of doc_num
     *
     * @return self
     */ 
    public function setDocNum($doc_num)
    {
        $this->doc_num = $doc_num;

        return $this;
    }

    /**
     * Get the value of qtd
     */ 
    public function getQtd()
    {
        return $this->qtd;
    }

    /**
     * Set the value of qtd
     *
     * @return self
     */ 
    public function setQtd($qtd)
    {
        $this->qtd = $qtd;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of last_updated_date
     */ 
    public function getLastUpdatedDate()
    {
        return $this->last_updated_date;
    }

    /**
     * Set the value of last_updated_date
     *
     * @return self
     */ 
    public function setLastUpdatedDate($last_updated_date)
    {
        $this->last_updated_date = $last_updated_date;

        return $this;
    }

    /**
     * Get the value of last_updated_user
     */ 
    public function getLastUpdatedUser()
    {
        return $this->last_updated_user;
    }

    /**
     * Set the value of last_updated_user
     *
     * @return self
     */ 
    public function setLastUpdatedUser($last_updated_user)
    {
        $this->last_updated_user = $last_updated_user;

        return $this;
    }
}