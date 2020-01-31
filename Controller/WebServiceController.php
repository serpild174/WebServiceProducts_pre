<?php

namespace WebServiceProducts\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Acme\Bundles\CustomThemeBundle\Entity\CreateTable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Acme\Bundles\CustomThemeBundle\Form\TaskType;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\ProductBundle\Migrations\Data\ORM\LoadProductDefaultAttributeFamilyData;
use Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\PricingBundle\Entity\BaseProductPrice;
use Oro\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\PricingBundle\Entity\BasePriceList;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\ProductBundle\Entity\Brand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\AbstractFixture;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductUnits;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\ProductBundle\Tests\Unit\Form\Type\ProductTypeTest;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductUnitRepository;
use Symfony\Component\HttpFoundation\File\Exception\CannotWriteFileException;


set_time_limit(0);

class WebServiceController extends AbstractController
{
    use UserUtilityTrait;
    const R = array();

    /**
     * @Route("/sync_products", name="sync_products_link")
     * @Template("CustomThemeBundle:CreateTable:index.html.twig")
     */
    public function indexAction()
    {
        return array('name' => "Sync Products");
    }

    public function getquerybyid($entityclass, $column_name, $parameter, $noid)
    {
        $entm = $this->getDoctrine()->getManager();
        if ($noid == false) {
            $query = $entm->createQuery(
                'SELECT u.id
                            FROM ' . $entityclass . ' u
                            WHERE u.' . $column_name . ' = :' . $column_name . ''
            )->setParameter('' . $column_name . '', $parameter);
        } elseif ($noid == true) {
            $query = $entm->createQuery(
                'SELECT u
                                FROM ' . $entityclass . ' u
                                WHERE u.' . $column_name . ' = :' . $column_name . ''
            )->setParameter('' . $column_name . '', $parameter);
        }

        if (!$parameter) {
            $query = $entm->createQuery(
                'SELECT u.id, u.name
                            FROM ' . $entityclass . ' u
                           '
            );
        }
        $getarray = $query->getResult();
        $array = json_decode(json_encode($getarray), true);

        return $array;
    }

    public function getunitcode($code)
    {

        /** @var ProductUnit $unit */
        $unitRepository = $this->getDoctrine()->getRepository(ProductUnit::class);
        $defaultProductunit = $unitRepository->getAllUnits();
        $unit_code = "";
        foreach ($defaultProductunit as $item) {
            $unit = $item;
            if ($code == $unit->getCode()) {
                $unit_code = $unit->getCode();
                $product_unit = $unit;
            }
        }
        return $product_unit;
    }

    /**
     * @Route("/sync_products_que_page_2", name="sync_products_que_2")
     * @Template("CustomThemeBundle:CreateTable:update.html.twig")
     * @Acl(
     *      id="sync_products_que_2",
     *      type="entity",
     *      class="OroProductBundle:Product",
     *      permission="EDIT"
     * )
     * @param Request $request
     */
    public function updateAction()
    {

        $entm = $this->getDoctrine()->getManager();

        $sku = "T757";

        $id = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\Product', 'sku', $sku, true);

        $array = json_decode(json_encode($id), true);
        //$id_edit = array('id'=>strval($id[0]['id']));
        $product_u = $entm->getRepository(Product::class)->find($array[0]['id']);
        $m = "";
        //$product=$entm->getRepository(Product::class)->getRequiredAttributesForSimpleProduct($product_u);
        /*$localized = new LocalizedFallbackValue();
        $localized->setString('hey');
        $entm->persist($localized);
        $entm->flush();*/

        if (!$product_u) {
            $m = "No product found";
        } elseif ($product_u) {
            $m = "Product updated.";
        }

        $name_localization = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue', 'string', $id[0]['name'], false);

        //$name_localization_get = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\Localization', 'name', 'Türkçe', false);
        $name_localization_get = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\Localization', 'name', '', false);
        $defaultname = (new LocalizedFallbackValue())->setString('Product Name Default');
        /*$product_u->setNames([$defaultname]);
        $entm->flush();
        foreach ($name_localization_get as $name) {
            $localization_get = $entm->getRepository(Localization::class)->find($name['id']);

            $product_u->addName(
                (new LocalizedFallbackValue())
                    ->setString('Product 3 '.$name['name'])
                    ->setLocalization($localization_get)
            );
            $entm->persist($product_u);
            $entm->flush();
        }*/

        $names = "";
        /** @var array|LocalizedFallbackValue[] $localization_array */
        /*foreach ($name_localization as $name) {
            $localization_u = $entm->getRepository(LocalizedFallbackValue::class)->find($name['id']);
            //$names_localization[]='newString';
            $localization_u->setString('yeni String');
            $entm->persist($localization_u);
            $names .= $name['id'];
            $localization_u->setLocalization($localization_get);
            //$localization_array=array('names'=>'yeni String');
            //$product_u->setNames($localization_array);
            //$product_u->addName($localization_u);
            //$product_u->setNames($localization_u);
            //$entm->flush();
            $entm->persist($product_u);
            $entm->flush();
            //array_push($names_localization, 'newString');
            //$product_u->removeName($localization_u);
        }*/
        /*$product_u->setNames([
            (new LocalizedFallbackValue())
                ->setString('Product Name'),
            (new LocalizedFallbackValue())
                ->setString('Product 3 Türkçe')
                ->setLocalization($localization_get),
        ]);
        $entm->persist($product_u);
        $entm->flush();*/
        $test = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\Localization', 'name', 'Türkçe', false);
        $test_2 = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\Product', 'sku', $sku, true);
        $test_3 = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue', 'string', $test_2[0]['name'], false);
        $test_4 = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\Localization', 'name', '', false);
        $test_5 = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision', 'product', $array[0]['id'], false);
        $test_6 = $this->getquerybyid('Oro\Bundle\InventoryBundle\Entity\InventoryLevel', 'product', $array[0]['id'], false);
        //$test_7 = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\ProductUnit', 'code', 'item', true);
        $pricelist = $this->getquerybyid('Oro\Bundle\PricingBundle\Entity\PriceList', 'name', '', false);
        $unit_precision_u = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision', 'product', $array[0]['id'], false);

        /*$inventory_query = $this->getquerybyid('Oro\Bundle\InventoryBundle\Entity\InventoryLevel', 'product', $array[0]['id'], false);

        $inventorylevel = $entm->getRepository(InventoryLevel::class)->find($inventory_query[0]['id']);
        $inventorylevel->setQuantity(110);
        $entm->flush();*/

        /*$unit=$this->getunitcode('each');
        $unitPrecision =$entm->getRepository(ProductUnitPrecision::class)->find($unit_precision_u[0]['id']);
                $unitPrecision->setUnit($unit);
        $product_u->setPrimaryUnitPrecision($unitPrecision);*/

        $quantity_count = array('1', '10', '20', '50', '100');
        $unit_product = $this->getunitcode($array[0]['unit']);

        /*for ($i = 0; $i < count($pricelist); $i++) {
            $default_pricelist = $entm->getRepository(PriceList::class)->find($pricelist[$i]['id']);
            for ($j = 0; $j < count($quantity_count); $j++) {
                $isproductprice_exits = $this->getquerybyid('Oro\Bundle\PricingBundle\Entity\ProductPrice', 'product', $array[0]['id'], false);

                $price = new ProductPrice();
                $price->setProduct($product_u)
                    ->setPriceList($default_pricelist)
                    ->setQuantity(floatval($quantity_count[$j]))
                    ->setUnit($unit_product)
                    ->setPrice(Price::create($quantity_count[$j], 'USD'));
                $entm->persist($price);
                $entm->flush();
            }
        }*/
        /*$default_pricelist = $entm->getRepository(PriceList::class)->find($pricelist[0]['id']);
        $unit_product = $this->getunitcode($array[0]['unit']);

        $price = new ProductPrice();
        $price->setProduct($product_u)
            ->setPriceList($default_pricelist)
            ->setQuantity(30)
            ->setUnit($unit_product)
            ->setPrice(Price::create('30.00', 'USD'));
        $entm->persist($price);
        $entm->flush();*/
        $prices="";
        $isproductprice_exits = $this->getquerybyid('Oro\Bundle\PricingBundle\Entity\ProductPrice', 'product', $array[0]['id'], false);
        if ($isproductprice_exits) {
            if (count($isproductprice_exits)>0) {
                for ($p=0; $p<count($isproductprice_exits); $p++) {
                    $price_u = $entm->getRepository(BaseProductPrice::class)->find($isproductprice_exits[$p]['id']);
                    //$price_u->setPrice(ProductPrice::create($webServicePrices[$i], 'USD'));
                    //$entm->flush();
                }
            }
        }
        return array('sku' => json_encode($isproductprice_exits[1]['id'])); //array_key_exists('name', $s),,array_keys(json_decode($s[0]))
        //json_encode(array_keys($array[0]))
    }

    public function newProductPrices(Product $product, $product_id, $product_unit , $price_value){
        $entm = $this->getDoctrine()->getManager();

        $pricelist = $this->getquerybyid('Oro\Bundle\PricingBundle\Entity\PriceList', 'name', '', false);
        $quantity_count = array('1', '10', '20', '50', '100');
        $unit_product = $this->getunitcode($product_unit);
        for ($i = 0; $i < count($pricelist); $i++) {
            $default_pricelist = $entm->getRepository(PriceList::class)->find($pricelist[$i]['id']);
            for ($j = 0; $j < count($quantity_count); $j++) {
                $isproductprice_exits = $this->getquerybyid('Oro\Bundle\PricingBundle\Entity\ProductPrice', 'product', $product_id, false);

                $price = new ProductPrice();
                $price->setProduct($product)
                    ->setPriceList($default_pricelist)
                    ->setQuantity(floatval($quantity_count[$j]))
                    ->setUnit($unit_product)
                    ->setPrice(Price::create($price_value*floatval($quantity_count[$j]), 'USD'));
                $entm->persist($price);
                $entm->flush();
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadProductUnits::class,
            //LoadVariantFields::class,
            //LoadProductMultiEnumValues::class
        ];
    }

    /**
     * {@inheritdoc}
     */


    /**
     * @Route("/sync_products_que_page", name="sync_products_que")
     * @Template("CustomThemeBundle:CreateTable:sync.html.twig")
     * @Acl(
     *      id="sync_products_que",
     *      type="entity",
     *      class="OroProductBundle:Product",
     *      permission="CREATE"
     * )
     * @param Request $request
     */
    public function createAction()
    {

        $now = new \DateTime();
        // you can fetch the EntityManager via $this->getDoctrine()
        // or you can add an argument to the action: createProduct(EntityManagerInterface $entityManager)
        $entm = $this->getDoctrine()->getManager();


        $inventoryStatusClassName = ExtendHelper::buildEnumValueClassName('prod_inventory_status');
        /** @var EntityManager $manager */
        /** @var AbstractEnumValue $inventoryStatus */
        $inventoryStatus = $entm->getRepository($inventoryStatusClassName)->find('in_stock');

        $user = $this->getFirstUser($entm);
        $businessUnit = $user->getOwner();
        $organization = $user->getOrganization();


        $familyRepository = $entm->getRepository(AttributeFamily::class);
        $defaultProductFamily = $familyRepository
            ->findOneBy(['code' => LoadProductDefaultAttributeFamilyData::DEFAULT_FAMILY_CODE]);


        $webServiceSku = $this->getWebServiceInfo()['Skus'];
        $webServiceNames = $this->getWebServiceInfo()['Names'];
        $webServiceUnits = $this->getWebServiceInfo()['Units'];
        $webServicePrices = $this->getWebServiceInfo()['Prices'];

        $entityManager = $this->getDoctrine()->getManager();

        $query = $entityManager->createQuery(
            'SELECT u.sku
                        FROM Oro\Bundle\ProductBundle\Entity\Product u'
        );
        $sku_s = $query->getResult();
        $sku = "";
        $update = false;
        $update_ch = array();
        $sku_item = "";

        $name_localization_get = $this->getquerybyid('Oro\Bundle\LocaleBundle\Entity\Localization', 'name', '', false);


        for ($i = 0; $i < count($this->newAction()['data']); $i++) {
            $update = false;
            for ($j = 0; $j < count($sku_s); $j++) {
                if ($sku_s[$j]['sku'] == $webServiceSku[$i]) {
                    $update = true;
                    $sku = $sku_s[$j]['sku'];
                    //$sku_item = $i;
                }
            }

            if ($update == true) {
                array_push($update_ch, 'true');
            } elseif ($update == false) {
                array_push($update_ch, 'false');
            }

            if ($update == true) {

                $id = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\Product', 'sku', $sku, true);

                $sku_item = $id[0]['id'];
                $product_u = $entm->getRepository(Product::class)->find($id[0]['id']);

                $defaultname = (new LocalizedFallbackValue())->setString($webServiceNames[$i]);
                $product_u->setNames([$defaultname]);
                $entm->flush();
                foreach ($name_localization_get as $name) {
                    $localization_get = $entm->getRepository(Localization::class)->find($name['id']);

                    $product_u->addName(
                        (new LocalizedFallbackValue())
                            ->setString($webServiceNames[$i]) //setString('Product 3 '.$name['name'])
                            ->setLocalization($localization_get)
                    );
                }

                $product_u->setStatus("enabled");
                $unit_precision_u = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\ProductUnitPrecision', 'product', $id[0]['id'], false);

                $inventory_query = $this->getquerybyid('Oro\Bundle\InventoryBundle\Entity\InventoryLevel', 'product', $id[0]['id'], false);

                $inventorylevel = $entm->getRepository(InventoryLevel::class)->find($inventory_query[0]['id']);
                $inventorylevel->setQuantity(110);

                $unit = $this->getunitcode('piece');
                $unitPrecision = $entm->getRepository(ProductUnitPrecision::class)->find($unit_precision_u[0]['id']);
                $unitPrecision->setUnit($unit);
                $product_u->setPrimaryUnitPrecision($unitPrecision);
                $brand_query = $this->getquerybyid('Oro\Bundle\ProductBundle\Entity\Brand', 'defaultTitle', 'Default ltd.', false);
                $brand_id = $entm->getRepository(Brand::class)->find($brand_query[0]['id']);
                $product_u->setBrand($brand_id);
                $entm->persist($product_u);
                $entm->flush();
                $isproductprice_exits = $this->getquerybyid('Oro\Bundle\PricingBundle\Entity\ProductPrice', 'product', $id[0]['id'], false);
                
                if ($isproductprice_exits) {
                    /*if (count($isproductprice_exits)>0) {
                        for ($p=0; $p<count($isproductprice_exits); $p++) {
                            $price_u = $entm->getRepository(BaseProductPrice::class)->find($isproductprice_exits[$p]['id']);
                            $price_u->setPrice(ProductPrice::create($webServicePrices[$i], 'USD'));
                            $entm->flush();
                        }
                    }*/
                } elseif (!$isproductprice_exits) {
                    $this->newProductPrices($product_u, $id[0]['id'], $id[0]['unit'], $webServicePrices[$i]);
                }

                //unset($webServiceSku[$sku_item]);
            } elseif ($update == false) {
                $unit = $this->getunitcode('piece');

                $unitPrecision = new ProductUnitPrecision();
                $unitPrecision->setUnit($unit)
                    ->setPrecision(0)
                    ->setConversionRate(1)
                    ->setSell(true);

                $product = new Product();
                $product->setSku($webServiceSku[$i]); //$this->getDynamicSku()

                $variantfields = array('field' => 'variantfield');
                $names = array($webServiceNames[$i]);

                $product
                    ->setOwner($businessUnit)
                    ->setOrganization($organization)
                    ->setAttributeFamily($defaultProductFamily)
                    ->setPrimaryUnitPrecision($unitPrecision)
                    ->setVariantFields($variantfields)
                    ->setCreatedAt($now)
                    ->setInventoryStatus($inventoryStatus)
                    ->setStatus("enabled")
                    ->setType("simple")
                    ->setFeatured(true)
                    ->setNewArrival(true);
                $defaultname = (new LocalizedFallbackValue())->setString($webServiceNames[$i]);
                $product->addName($defaultname);
                foreach ($name_localization_get as $name) {
                    $localization_get = $entm->getRepository(Localization::class)->find($name['id']);

                    $product->addName(
                        (new LocalizedFallbackValue())
                            ->setString($webServiceNames[$i]) //setString('Product 3 '.$name['name'])
                            ->setLocalization($localization_get)
                    );
                }
                $entm->persist($product);
                $entm->flush();
                $inventory_query = $this->getquerybyid('Oro\Bundle\InventoryBundle\Entity\InventoryLevel', 'product', $product->getId(), false);

                $inventorylevel = $entm->getRepository(InventoryLevel::class)->find($inventory_query[0]['id']);
                $inventorylevel->setQuantity(110);
                $entm->flush();
                $this->newProductPrices($product, $product->getId(), 'piece', $webServicePrices[$i]);
            }
        }


        //return new Response('Saved new product with id ' . $product->getId());
        //return new Response('<html><body>Product created!</body></html>');
        return array('sku' => json_encode($update_ch));
    }

    /**
     * @Route("/sync_products_web_service", name="sync_products_response")
     * @Template("CustomThemeBundle:CreateTable:webservice.html.twig")
     * @param Request $request
     */
    public function getWebServiceArray()
    {
        return array('response' => json_encode($this->newAction()));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function newAction()
    {
        $url = "http://webservice.tsoft.com.tr/rest1/product/getProducts";

        $fields = array('token' => 'e15085v91h80ri68b806c2okqa');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $response = curl_exec($ch);
        //$result=json_decode($response);
        curl_close($ch);

        /*$jsonIterator = new RecursiveIteratorIterator(
                new RecursiveArrayIterator(json_decode($response, TRUE)),
                RecursiveIteratorIterator::SELF_FIRST);

            foreach ($jsonIterator as $key => $val) {
                if(is_array($val)) {
                    echo "$key:";
                    echo "\n";
                } else {
                    echo "$key => $val\n";
                }
            }*/
        $array = json_decode($response, true);
        /*foreach ($array['data'] as $item) {
            array_push($this->R, $item);
        }
        $data = $array['data'];*/
        //print_r($response);
        return $array;
        //print_r($data['2']['ProductId']);
        /*for($i=0;$i<count($data);$i++){
                echo $data[$i]['ProductId']."<br>";
            }*/
    }

    /** @return string */
    public function getDynamicSku()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $query = $entityManager->createQuery(
            'SELECT u.sku
                FROM Oro\Bundle\ProductBundle\Entity\Product u'
        );
        $sku_s = $query->getResult();

        //md5(uniqid(mt_rand(), true).microtime(true));
        //md5(substr(shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5).microtime(true));
        for ($i = 0; $i < count($sku_s); $i++) {
            //$skus.= $sku_s[$i]['sku']."\n";
            $rand_sku = $this->getRandomWord();
            if ($sku_s[$i]['sku'] == $rand_sku) {
                $rand_sku = $this->getRandomWord();
            }
        }

        return $rand_sku;
    }

    /** @return array */
    public function getWebServiceInfo()
    {
        $response = $this->newAction();
        $data = array();
        $data = $response['data'];
        $sku = array();
        $unit = array();
        $name = array();
        $status = array();
        $price = array();

        for ($i = 0; $i < count($data); $i++) {
            $unit[$i] = $data[$i]['StockUnit'];
            $sku[$i] = $data[$i]['ProductCode'];
            $name[$i] = $data[$i]['ProductName'];
            $status[$i] = $data[$i]['IsActive'];
            $price[$i] = $data[$i]['SellingPrice'];
        }
        $info = array('Units' => $unit, 'Skus' => $sku, 'Names' => $name, 'Status' => $status, 'Prices' => $price);

        return $info;
    }


    /**
     * @Route("/sync_products_controls", name="sync_products_contoller")
     * @Template("CustomThemeBundle:CreateTable:controls.html.twig")
     * @param Request $request
     */
    public function controlAction()
    {
        return array('array' => json_encode($this->getWebServiceInfo()));
    }

    public function getRandomWord($len = 5)
    {
        $word = array_merge(range('A', 'Z'), range('1', '9'));
        shuffle($word) . microtime(true);
        return substr(implode($word), 0, $len);
    }

    /**
     * @Route("/sync_products_random", name="sync_products_random_query")
     * @Template("CustomThemeBundle:CreateTable:rand.html.twig")
     * @param Request $request
     */
    public function randomword()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $query = $entityManager->createQuery(
            'SELECT u.sku
                FROM Oro\Bundle\ProductBundle\Entity\Product u'
        );
        $sku_s = $query->getResult();

        //md5(uniqid(mt_rand(), true).microtime(true));
        //md5(substr(shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5).microtime(true));
        for ($i = 0; $i < count($sku_s); $i++) {
            //$skus.= $sku_s[$i]['sku']."\n";
            $rand_sku = $this->getRandomWord();
            if ($sku_s[$i]['sku'] == $rand_sku) {
                $rand_sku = $this->getRandomWord();
            }
        }

        return array('sku' => $rand_sku); //$this->json($sku_s)
    }

    public function load(ObjectManager $manager)
    {
    }

    private function update(CreateTable $createtable, Request $request)
    {
        $form = $this->createForm(new TaskType(), $createtable);

        return [
            'entity' => $createtable,
            'form' => $form->createView(),
        ];
    }
}
