<?php
use Events\Model\{Form};
use MVC\Model\Form\Save;
class FormTest extends PHPUnit\Framework\TestCase {
    public function testMain() {
        $saveMock = $this->getMockBuilder(Save::class)
                         ->setMethods(['main'])
                         ->getMock();
        $saveMock->expects($this->once())
                 ->method('main')
                 ->with($this->equalTo(['test']));

        $form = new Form($saveMock);

        $form->main(['test']);
    }

    public function testGetData() {
        $saveMock = $this->getMockBuilder(Save::class)
                         ->setMethods(['getData'])
                         ->getMock();
        $saveMock->expects($this->once())
                 ->method('getData')
                 ->willReturn('test');

        $form = new Form($saveMock);

        $this->assertEquals('test', $form->getData());
    }

    public function testSave() {

    }
}
