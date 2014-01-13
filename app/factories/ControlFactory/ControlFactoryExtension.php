<?php

namespace HotelQuickly\Factory;

use Nette,
	Nette\DI\Compiler,
	Nette\DI\ContainerBuilde;

/**
 *  @author Jan Mikes <jan.mikes@hotelquickly.com>
 *  @copyright HotelQuickly Ltd.
 */
class ControlFactoryExtension extends Nette\Config\CompilerExtension {

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$definitions = $builder->getDefinitions();

		$templateFactory = $builder->getDefinition("templateFactory");

		foreach ($definitions as $definition) {
			if (!$definition->factory || !class_exists($definition->factory->entity)) {
				continue;
			}

			$classReflection = new Nette\Reflection\ClassType($definition->factory->entity);
			if ($classReflection->implementsInterface("HotelQuickly\Factory\IControlFactory")) {
				$definition->addSetup("setTemplateFactory", array($templateFactory));
			}
		}

	}
}
