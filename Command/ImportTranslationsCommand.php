<?php

namespace EMS\CoreBundle\Command;

use EMS\CoreBundle\Service\DataService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImportTranslationsCommand extends EmsCommand
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var DataService */
    protected $dataService;

    public function __construct(LoggerInterface $logger, DataService $dataService)
    {
        $this->logger = $logger;
        $this->dataService = $dataService;
        parent::__construct($logger, null);
    }

    protected function configure()
    {
        $this
            ->setName('ems:contenttype:translations')
            ->setDescription('Import XLIFF translations and update documents (documents are finalized in the default environment)')
            ->addArgument(
                'xliffPath',
                InputArgument::REQUIRED,
                'Path to the XLIFF files folder, i.e. ~/Downloads/xliff/*.xliff'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->notice('command.import_translations.start', [
        ]);

        $this->formatStyles($output);

        $finder = new Finder();
        $finder->files()->name('*.xliff')->in($input->getArgument('xliffPath'));
        $total = 0;

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $output->writeln($file->getRealPath());
                $crawler = new Crawler($file->getContents());

                $original = \preg_split('/\//', $crawler->filterXPath('//xliff//file')->attr('original'));
                $contentType = end($original);

                $revisions = [];

                $crawler->filter('trans-unit')->each(function (Crawler $node, $i) use(&$contentType, &$output, &$total, &$revisions) {
                    $fieldId = \preg_split('/\:/', $node->attr('id'));
                    $ouuid = $fieldId[0];
                    try {

                        if (isset($revisions[$ouuid])) {
                            $revision = $revisions[$ouuid];
                        }
                        else {
                            $revision = $this->dataService->initNewDraft($contentType, $ouuid, null, 'IMPORT_TRANSLATIONS');
                            $revisions[$ouuid] = $revision;
                        }
                        $source = $node->filter('source');
                        $target = $node->filter('target');
                        $sourceLocale = strtolower($source->attr('xml:lang'));
                        $targetLocale = strtolower($target->attr('xml:lang'));

                        $rawData = $revision->getRawData();

                        $sourceValue = $this->array_get_value($rawData, $fieldId[1].$sourceLocale);
                        $targetValue = $this->array_get_value($rawData, $fieldId[1].$targetLocale);
                        if ($sourceValue) {
                            if ($source->text() !== $sourceValue) {
                                $output->writeln(sprintf('<comment>Source field %s has been changed in the documents %s:%s</comment>', $fieldId[1].$sourceLocale, $contentType, $ouuid));
//                                $output->writeln($source->text());
//                                $output->writeln($rawData[$fieldId[1].$sourceLocale]);
                            }
                            if ( $targetValue && $targetValue === $target->text() ) {
                                $output->writeln(sprintf('Source field %s has already the good value for the documents %s:%s', $fieldId[1].$targetLocale, $contentType, $ouuid));
//                                $this->dataService->discardDraft($revision, 'IMPORT_TRANSLATIONS');
                            } else {
                                $this->array_set_value($rawData, $fieldId[1].$targetLocale, $target->text());
                                $revision->setRawData($rawData);
                                ++ $total;
//                                $this->dataService->finalizeDraft($revision, $form, 'IMPORT_TRANSLATIONS');
                            }
                        }
                        else {
                            $output->writeln(sprintf('<comment>Source field %s not found in the documents %s:%s</comment>', $fieldId[1].$sourceLocale, $contentType, $ouuid));
//                            $this->dataService->discardDraft($revision, 'IMPORT_TRANSLATIONS');
                        }
                    }
                    catch (NotFoundHttpException $e) {
                        $output->writeln(sprintf('<comment>Documents %s:%s not found</comment>', $contentType, $fieldId[0]));
                    }
                });

                foreach ($revisions as $revision ) {
                    $form = $this->dataService->buildForm($revision);
                    $this->dataService->finalizeDraft($revision, $form, 'IMPORT_TRANSLATIONS');
                }
            }
        }
        else {
            $output->writeln('No file found');
        }

        $output->writeln(sprintf('%d translations imported', $total));
        $this->logger->notice('command.import_translations.end', [
            'total' => $total,
        ]);
    }


    /**
     * @param array $array
     * @param array|string $parents
     * @param string $glue
     * @return mixed
     */
    function array_get_value(array &$array, $parents, $glue = '.')
    {
        if (!is_array($parents)) {
            $parents = explode($glue, $parents);
        }

        $ref = &$array;

        foreach ((array) $parents as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            } else {
                return null;
            }
        }
        return $ref;
    }

    /**
     * @param array $array
     * @param array|string $parents
     * @param mixed $value
     * @param string $glue
     */
    function array_set_value(array &$array, $parents, $value, $glue = '.')
    {
        if (!is_array($parents)) {
            $parents = explode($glue, (string) $parents);
        }

        $ref = &$array;

        foreach ($parents as $parent) {
            if (isset($ref) && !is_array($ref)) {
                $ref = array();
            }

            $ref = &$ref[$parent];
        }

        $ref = $value;
    }
}
