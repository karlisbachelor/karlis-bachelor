<?php declare(strict_types=1);


namespace Karlis\Vat\Controller\Index;


class ValidateAndApply extends \Magento\Framework\App\Action\Action
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
     * @var \Karlis\Vat\Helper\Data
     */
    protected $data;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Karlis\Vat\Helper\Data $data
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->data = $data;
    }

    /**
     * Execute view action
     */
    public function execute()
    {
        try {
            $this->jsonResponse(['resource_id' => $this->data->getResourceId()]);


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->jsonResponse('', $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->jsonResponse('', $e->getMessage());
        }
    }

    /**
     * JSON response builder.
     *
     * @param $data
     * @param string $error
     */
    private function jsonResponse($data = '', string $error = '')
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
    private function getResponseData($data = '', string $error = ''): array
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
