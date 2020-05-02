<?php


namespace Karlis\Vat\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Phrase;

/**
 * Class DataGov.
 *
 * API implementation for data.gov.lc
 */
class DataGov
{
    const API_URL = 'https://data.gov.lv/dati/lv/api/3/action/datastore_search_sql';
    const FIELD_SEPA = 'sepa';

    /**
     * @var \Karlis\Vat\Helper\Data
     */
    protected $karlisVatHelper;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var string
     */
    protected $resourceId;

    /**
     * @var string
     */
    protected $sepa;

    /**
     * @var string
     */
    protected $response;

    /**
     * DataGov constructor.
     *
     * @param \Karlis\Vat\Helper\Data $karlisVatHelper
     * @param Curl $curl
     * @param EncoderInterface $urlEncoder
     */
    public function __construct(
        \Karlis\Vat\Helper\Data $karlisVatHelper,
        Curl $curl
    ) {
        $this->karlisVatHelper = $karlisVatHelper;
        $this->curl = $curl;
    }

    /**
     * Run API request and save response in $this->response
     *
     * @return DataGov
     * @throws LocalizedException
     */
    public function run()
    {
        try {
            $url = self::API_URL . '?sql=' . urlencode($this->getSQL());

            $this->getCurlClient()->get($url);

            // We are saving response to the variable, in case if we need to get response value in multiple places.
            // So, we can just look for this value instead of multiple requests to the data.gov.lv
            $this->setResponse($this->getCurlClient()->getBody());

        } catch (\Exception $e) {
            throw new LocalizedException(
                new Phrase($e->getMessage())
            );
        }

        return $this;
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curl;
    }

    /**
     * Get necessary SQL for API call.
     *
     * @return string
     * @throws LocalizedException
     */
    protected function getSQL()
    {
        if (!$this->getResourceId() && !$this->getSepa()) {
            throw new LocalizedException(
                new Phrase('Missing resource_id and sepa.', [get_class($this)])
            );
        }

        return 'SELECT * from "' . $this->getResourceId() . '" WHERE ' . self::FIELD_SEPA . ' = \'' . $this->getSepa() . '\'';
    }

    /**
     * @param string $resourceId
     * @return DataGov
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId ?: null;
    }

    /**
     * @param string $sepa
     * @return DataGov
     */
    public function setSepa($sepa)
    {
        $this->sepa = $sepa;
        return $this;
    }

    /**
     * @return string
     */
    public function getSepa()
    {
        return $this->sepa ?: null;
    }

    /**
     * The method is proteced to prevent set response from outside. Only current class and its childes can set response.
     *
     * @param string $response
     * @return DataGov
     */
    protected function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response ?: '';
    }
}
