<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under following license:
 * - Pimcore Commercial License (PCL)
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\Command\Update;

use Exception;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\IndexQueueService;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\IndexUpdateService;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

class IndexUpdateCommand extends AbstractCommand
{
    use LockableTrait;

    const OPTION_CLASS_DEFINITION_ID = 'class-definition-id';

    const OPTION_UPDATE_ASSET_INDEX = 'update-asset-index';

    const OPTION_RECREATE_INDEX = 'recreate_index';

    protected IndexUpdateService $indexUpdateService;

    protected IndexQueueService $indexQueueService;

    protected function configure()
    {
        $this
            ->setName('generic-data-index:update:index')
            ->addOption(self::OPTION_CLASS_DEFINITION_ID, 'cid', InputOption::VALUE_OPTIONAL, 'Update mapping and data for specific data object classDefinition', null)
            ->addOption(self::OPTION_UPDATE_ASSET_INDEX, 'a', InputOption::VALUE_NONE, 'Update mapping and data for asset index', null)
            ->addOption(self::OPTION_RECREATE_INDEX, 'r', InputOption::VALUE_NONE, 'Delete OpenSearch index and recreate it', null)
            ->setDescription('Updates index/mapping for all classDefinitions/asset without deleting them. Adds there elements to index queue.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            throw new Exception('The command is already running in another process.');
        }

        $this->indexUpdateService->setReCreateIndex($input->getOption(self::OPTION_RECREATE_INDEX));

        $updateAll = true;

        /** @var string|null $classDefinitionId */
        $classDefinitionId = $input->getOption(self::OPTION_CLASS_DEFINITION_ID);

        if ($classDefinitionId) {
            $updateAll = false;

            try {
                $classDefinition = ClassDefinition::getById($classDefinitionId);
                if (!$classDefinition) {
                    throw new Exception(sprintf('ClassDefinition with id %s not found', $classDefinitionId));
                }

                $this->output->writeln(sprintf('<info>Update index and indices for ClassDefinition with id %s</info>', $classDefinitionId), OutputInterface::VERBOSITY_VERBOSE);

                $this
                    ->indexUpdateService
                    ->updateClassDefinition($classDefinition);
            } catch (Exception $e) {
                $this->output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        if ($input->getOption(self::OPTION_UPDATE_ASSET_INDEX)) {
            $updateAll = false;

            try {
                $output->writeln('<info>Update asset index</info>', OutputInterface::VERBOSITY_VERBOSE);

                $this
                    ->indexUpdateService
                    ->updateAssets();
            } catch (Exception $e) {
                $this->output->writeln($e->getMessage());
            }
        }

        if ($updateAll) {
            $this->output->writeln('<info>Update all mappings and indices for objects/assets</info>', OutputInterface::VERBOSITY_VERBOSE);

            $this
                ->indexUpdateService
                ->updateAll();
        }

        $this->indexQueueService->dispatchQueueMessages($output);

        $this->release();

        $this->output->writeln('<info>Finished</info>', OutputInterface::VERBOSITY_VERBOSE);

        return self::SUCCESS;
    }

    #[Required]
    public function setIndexUpdateService(IndexUpdateService $indexUpdateService): void
    {
        $this->indexUpdateService = $indexUpdateService;
    }

    #[Required]
    public function setIndexQueueService(IndexQueueService $indexQueueService): void
    {
        $this->indexQueueService = $indexQueueService;
    }
}
