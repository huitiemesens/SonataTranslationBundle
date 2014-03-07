<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\TranslationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Nicolas Bastien <nbastien.pro@gmail.com>
 */
class SonataTranslationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('sonata_translation.locales', $config['locales']);
        $container->setParameter('sonata_translation.default_locale', $config['default_locale']);

        $isEnabled = false;
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $translationInterfaces = array();
        if ($config['gedmo']['enabled']) {
            $isEnabled = true;
            $loader->load('service_gedmo.xml');

            $translationInterfaces['gedmo'] = array_merge(
                array('Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface'),
                $config['gedmo']['interfaces']
            );

        }
        if ($config['phpcr']['enabled']) {
            $isEnabled = true;
            $loader->load('service_phpcr.xml');

            $translationInterfaces['phpcr'] = array_merge(
                array('Sonata\TranslationBundle\Model\Phpcr\TranslatableInterface'),
                $config['phpcr']['interfaces']
            );
        }

        if ($isEnabled === true) {
            $loader->load('block.xml');
            $loader->load('listener.xml');
            $loader->load('twig.xml');
        }

        $container->setParameter('sonata_translation.interfaces', $translationInterfaces);
    }
}
