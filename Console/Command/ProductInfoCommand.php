<?php
/**
 * @category    Ruchlewicz
 * @package     Ruchlewicz_ConsoleCommand
 * @author      Sebastian Ruchlewicz <sebastian.ruchlewicz@gmail.com>
 * @copyright   Copyright (c) Sebastian Ruchlewicz (https://ruchlewicz.net/)
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace Ruchlewicz\ConsoleCommand\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SimpleDataObjectConverter;

class ProductInfoCommand extends \Symfony\Component\Console\Command\Command
{
    const PRODUCT_ID_ARGUMENT_CODE = 'product-id';
    const ATTRIBUTE_NAME_OPTION_CODE = 'attribute';
    const ATTRIBUTE_NAME_OPTION_CODE_SHORTCUT = 'a';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * SampleCommand constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param State $state
     * @param null $name
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        State $state,
        $name = null
    ) {
        parent::__construct($name);
        $this->productRepository = $productRepository;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ruchlewicz:product-info')
            ->setDescription('Get product info');

        $this->addArgument(
            self::PRODUCT_ID_ARGUMENT_CODE,
            InputArgument::REQUIRED,
            'Id of the product'
        );

        $this->addOption(
            self::ATTRIBUTE_NAME_OPTION_CODE,
            self::ATTRIBUTE_NAME_OPTION_CODE_SHORTCUT,
            InputOption::VALUE_REQUIRED,
            'Attribute of the product you want to display'
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            // Area code was already set, so we do not have to do anything after catching exception
        }
        $productId = $input->getArgument(self::PRODUCT_ID_ARGUMENT_CODE);
        $output->writeln('<info>Searching for product with id - ' . $productId . '</info>');

        try {
            $product = $this->productRepository->getById($productId);

            if ($attributeCode = $input->getOption(self::ATTRIBUTE_NAME_OPTION_CODE)) {
                $value = $this->getProductAttributeValue($product, $attributeCode);
                $output->writeln('<info>value of ' . $attributeCode . ' - "' .$value . '"</info>');
            } else {
                $values = $this->getProductAllAttributeValues($product);
                $output->writeln('<info>' . $values . '</info>');
            }
        } catch (NoSuchEntityException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return $e->getCode();
        }

        return 0;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $attributeCode
     * @return string|null
     */
    private function getProductAttributeValue($product, $attributeCode)
    {
        $objectMethods = $this->getObjectMethods($product);
        $camelCaseAttributeGetter = SimpleDataObjectConverter::snakeCaseToCamelCase('get_' . $attributeCode);

        if (in_array($camelCaseAttributeGetter, $objectMethods)) {
            return $product->{$camelCaseAttributeGetter}();
        }

        return $product->getCustomAttribute($attributeCode);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    private function getProductAllAttributeValues($product)
    {
        $objectMethods = $this->getObjectMethods($product);
        $value = '';

        foreach ($objectMethods as $method) {
            if (strpos($method, 'get') === 0) {
                $value .= $this->parseObjectFunctionValue($product, $method) ?
                    $this->parseObjectFunctionValue($product, $method). "\n\n"
                    : '';
            }
        }

        return $value;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $method
     * @return string
     */
    private function parseObjectFunctionValue($product, $method)
    {
        try {
            $returnValue = $product->{$method}();
        } catch (\ArgumentCountError $e) {
            return '';
        }

        if (is_object($returnValue)) {
            $returnValue = get_class($returnValue);
        } elseif (is_array($returnValue)) {
            $returnValue = json_encode($returnValue);
        }

        return $method . ' => ' . $returnValue;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return array
     */
    private function getObjectMethods($product)
    {
        return get_class_methods(get_class($product));
    }
}
