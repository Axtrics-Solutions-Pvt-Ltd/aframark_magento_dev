<?php
namespace Axtrics\Aframark\Controller\Catalog;

class Count extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	/*
	* Product collection variable declare
	*/
	protected $productCollection;
	/*
	* Json response set variable declare
	*/
	protected $resultJsonFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->productCollection = $productCollectionFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		die("oookkk");
		$collection = $this->productCollection->create();
		$collection=$collection->load();
		$count= $collection->count();
		$result = $this->resultJsonFactory->create();

    $result->setData(["Status"=>200,'count' =>$count ]);
    return $result; 
    exit;
	}
	// public function getPost(){
	// 	die("getpost");
	// }
}
