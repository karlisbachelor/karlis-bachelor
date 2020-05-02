<?php


namespace Karlis\Vat\Controller\Index;


use Magento\Framework\App\Action\Context;

abstract class AbstractJsonResponse extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * AbstractJsonResponse constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * JSON response builder.
     *
     * @param $data
     * @param string $error
     */
    protected function jsonResponse($data = '', string $error = '')
    {
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->getResponseData($data, $error))
        );
    }

    /**
     * Returns response data.
     *
     * @param $data
     * @param string $error
     * @return array
     */
    protected function getResponseData($data = '', string $error = ''): array
    {
        $response = ['data' => $data, 'success' => true];

        if (!empty($error)) {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }

        return $response;
    }
}
