<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pixelant\PxaProductManager\Domain\Model\Category;

/**
 * @package Pixelant\PxaProductManager\Tests\Unit\Domain\Model
 */
class CategoryTest extends UnitTestCase
{
    protected $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new Category();
    }

    /**
     * @test
     */
    public function getParentsRootLineReturnRootLineOfParents()
    {
        $lastCategory = $this->rootLineLastCategory();

        $this->subject->setParent($lastCategory);
        $this->subject->_setProperty('uid', 100);

        // Vise versa
        $expect = [100, 5, 4, 3, 2, 1];
        $result = array_map(fn($cat) => $cat->getUid(), $this->subject->getParentsRootLine());

        // Compare  UIDs, because object won't be same
        $this->assertEquals($expect, $result);
    }

    /**
     * @test
     */
    public function getParentsRootLineReverseReturnReversedRootLine()
    {
        $lastCategory = $this->rootLineLastCategory();

        $this->subject->setParent($lastCategory);
        $this->subject->_setProperty('uid', 100);

        $expect = [1, 2, 3, 4, 5, 100];
        $result = array_map(fn($cat) => $cat->getUid(), $this->subject->getParentsRootLineReverse());

        // Compare  UIDs, because object won't be same
        $this->assertEquals($expect, $result);
    }

    /**
     * @test
     */
    public function getParentsRootLineReturnRootLineOfParentsButStopIfLoopFound()
    {
        $root = makeDomainInstanceWithProperties(Category::class, 99);
        $subCat1 = makeDomainInstanceWithProperties(Category::class, 1);
        $subCat2 = makeDomainInstanceWithProperties(Category::class, 2);
        $subCat3 = makeDomainInstanceWithProperties(Category::class, 3);

        $subCat3->setParent($subCat2);
        $subCat2->setParent($subCat1);
        $subCat1->setParent($root);

        // Here we have possible loop
        $root->setParent($subCat3);

        $this->subject->setParent($subCat3);
        $this->subject->_setProperty('uid', 100);

        $expect = [100, 3, 2, 1, 99];
        $result = array_map(fn($cat) => $cat->getUid(), $this->subject->getParentsRootLine());

        $this->assertEquals($expect, $result);
    }

    public function rootLineLastCategory()
    {
        $rootLine = makeMultipleDomainsInstances(Category::class, 5);

        // Simulate rootline
        $prev = null;
        foreach ($rootLine as $category) {
            if ($prev !== null) {
                $category->setParent($prev);
            }
            $prev = $category;
        }

        return $prev;
    }
}