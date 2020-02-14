<?php
namespace Axtrics\Aframark\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
class BeforeDeleteProduct implements ObserverInterface
{
   /**
     * @param Observer $observer
     *
     */
     private $eventManager;
   public function __construct(
            ProductRepositoryInterface $productRepository,
            EventManager $eventManager
            )
            {
            $this->productRepository = $productRepository;
            $this->eventManager = $eventManager;
            }
    protected $productRepository; 
    public function execute(Observer $observer)
    {
    
    }

}

