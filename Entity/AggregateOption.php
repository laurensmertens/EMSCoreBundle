<?php

namespace EMS\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataField
 *
 * @ORM\Table(name="aggregate_option")
 * @ORM\Entity(repositoryClass="EMS\CoreBundle\Repository\AggregateOptionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AggregateOption
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name; 
    
    /**
     * @var string
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;
    
    /**
     * @var string
     *
     * @ORM\Column(name="template", type="text", nullable=true)
     */
    private $template;
    
    /**
     * @var int
     *
     * @ORM\Column(name="orderKey", type="integer")
     */
    private $orderKey;
    
    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="text", length=255, nullable=true)
     */
    private $icon;
    
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateModified()
    {
    	$this->modified = new \DateTime();
        if(!isset($this->created)){
    		$this->created = $this->modified;
    	}
    	if(!isset($this->orderKey)){
    		$this->orderKey = 0;
    	}
    }
    
    /******************************************************************
     * 
     * Generated functions
     * 
     *******************************************************************/

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return AggregateOption
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return AggregateOption
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return AggregateOption
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set config
     *
     * @param string $config
     *
     * @return AggregateOption
     */
    public function setConfig($config)
    {
    	$this->config= $config;

        return $this;
    }
    
    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
    	return $this->template;
    }
    
    /**
     * Set template
     *
     * @param string $config
     *
     * @return AggregateOption
     */
    public function setTemplate($template)
    {
    	$this->template= $template;
    	
    	return $this;
    }
    
    /**
     * Get config
     *
     * @return string
     */
    public function getConfig()
    {
    	return $this->config;
    }

    /**
     * Set orderKey
     *
     * @param integer $orderKey
     *
     * @return AggregateOption
     */
    public function setOrderKey($orderKey)
    {
        $this->orderKey = $orderKey;

        return $this;
    }

    /**
     * Get orderKey
     *
     * @return integer
     */
    public function getOrderKey()
    {
        return $this->orderKey;
    }
    
    /**
     * Set icon
     *
     * @param boolean $icon
     *
     * @return SortOption
     */
    public function setIcon($icon)
    {
    	$this->icon= $icon;
    	
    	return $this;
    }
    
    /**
     * Get icon
     *
     * @return boolean
     */
    public function getIcon()
    {
    	return $this->icon;
    }
}