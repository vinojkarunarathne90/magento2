<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Manage currency block
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate;

class Matrix extends \Magento\Backend\Block\Template
{
    protected $_template = 'system/currency/rate/matrix.phtml';

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_adminSession;

    /**
     * @var \Magento\Directory\Model\Currency\Factory
     */
    protected $_dirCurrencyFactory;

    /**
     * @param \Magento\Directory\Model\Currency\Factory $dirCurrencyFactory
     * @param \Magento\Backend\Model\Session $adminSession
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Directory\Model\Currency\Factory $dirCurrencyFactory,
        \Magento\Backend\Model\Session $adminSession,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_dirCurrencyFactory = $dirCurrencyFactory;
        $this->_adminSession = $adminSession;
        parent::__construct($coreData, $context, $data);
    }

    protected function _prepareLayout()
    {
        $newRates = $this->_adminSession->getRates();
        $this->_adminSession->unsetData('rates');

        $currencyModel = $this->_dirCurrencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
        $oldCurrencies = $this->_prepareRates($currencyModel->getCurrencyRates($defaultCurrencies, $currencies));

        foreach ($currencies as $currency) {
            foreach ($oldCurrencies as $key => $value) {
                if (!array_key_exists($currency, $oldCurrencies[$key])) {
                    $oldCurrencies[$key][$currency] = '';
                }
            }
        }

        foreach ($oldCurrencies as $key => $value) {
            ksort($oldCurrencies[$key]);
        }

        sort($currencies);

        $this->setAllowedCurrencies($currencies)
            ->setDefaultCurrencies($defaultCurrencies)
            ->setOldRates($oldCurrencies)
            ->setNewRates($this->_prepareRates($newRates));

        return parent::_prepareLayout();
    }

    public function getRatesFormAction()
    {
        return $this->getUrl('adminhtml/*/saveRates');
    }

    protected function _prepareRates($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        foreach ($array as $key => $rate) {
            foreach ($rate as $code => $value) {
                $parts = explode('.', $value);
                if (sizeof($parts) == 2) {
                    $parts[1] = str_pad(rtrim($parts[1], 0), 4, '0', STR_PAD_RIGHT);
                    $array[$key][$code] = join('.', $parts);
                } elseif ($value > 0) {
                    $array[$key][$code] = number_format($value, 4);
                } else {
                    $array[$key][$code] = null;
                }
            }
        }
        return $array;
    }
}