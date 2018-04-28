<?php

// src/EMS/CoreBundle/Command/GreetCommand.php
namespace EMS\CoreBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Elasticsearch\Client;
use EMS\CoreBundle\Controller\AppController;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Repository\JobRepository;
use EMS\CoreBundle\Service\ContentTypeService;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;
use EMS\CoreBundle\Service\EnvironmentService;

class ContentTypeCommand extends ContainerAwareCommand
{

    /**@var Logger */
    private $logger;
    /**@var ContentTypeService */
    private $contentTypeService;

    public function __construct(Logger $logger, ContentTypeService $contentTypeService)
    {
        $this->logger = $logger;
        $this->contentTypeService = $contentTypeService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ems:contenttype:list')
            ->setDescription('List the content types defined')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'List all content types (by default list managed content types only)'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypes = $this->contentTypeService->getAll();

        $table = new Table($output);

        $table->setHeaders([
            'Created',
            'Modified',
            'Name',
            'Singular',
            'Plural',
            'Managed',
            'Active',
            'Dirty',
            'Root',
            'Web Content',
        ]);

        /** @var ContentType $contentType */
        foreach ($contentTypes as $contentType) {
            if ($contentType->getEnvironment()->getManaged() || $input->getOption('all')) {
                $table->addRow([
                    $contentType->getCreated()->format('c'),
                    $contentType->getModified()->format('c'),
                    $contentType->getName(),
                    $contentType->getSingularName(),
                    $contentType->getPluralName(),
                    $contentType->getEnvironment()->getManaged() ? 'true' : 'false',
                    $contentType->getActive() ? 'true' : 'false',
                    $contentType->getDirty() ? 'true' : 'false',
                    $contentType->getRootContentType() ? 'true' : 'false',
                    $contentType->getWebContent() ? 'true' : 'false',
                ]);

            }

        }
        $table->render();
    }


}
