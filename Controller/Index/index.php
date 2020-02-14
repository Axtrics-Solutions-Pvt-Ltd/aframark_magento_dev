<?php
namespace Axtrics\Aframark\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Request\Http $request,
		\Psr\Log\LoggerInterface $loggerInterface,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->request = $request;
		$this->logger = $loggerInterface;
		return parent::__construct($context);
	}

	public function execute()
	{
		  	print_r($this->getRequest()->getParams());
	
	}
}
