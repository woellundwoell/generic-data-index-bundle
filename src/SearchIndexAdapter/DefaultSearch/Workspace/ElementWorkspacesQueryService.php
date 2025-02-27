<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\DefaultSearch\Workspace;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\Permission\UserPermissionTypes;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\DefaultSearch\ConditionType;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\ElementType;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\FieldCategory\SystemField;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\BoolQuery;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\TermFilter;
use Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\TermsFilter;
use Pimcore\Bundle\GenericDataIndexBundle\Permission\Workspace\AssetWorkspace;
use Pimcore\Bundle\GenericDataIndexBundle\Permission\Workspace\DataObjectWorkspace;
use Pimcore\Bundle\GenericDataIndexBundle\Permission\Workspace\DocumentWorkspace;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\Workspace\ElementWorkspacesQueryServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\SearchIndexAdapter\Workspace\QueryServiceInterface;
use Pimcore\Bundle\GenericDataIndexBundle\Service\Permission\UserPermissionServiceInterface;
use Pimcore\Model\User;

/**
 * @internal
 */
final readonly class ElementWorkspacesQueryService implements ElementWorkspacesQueryServiceInterface
{
    private const WORKSPACE_PERMISSION_MAPPING = [
        AssetWorkspace::WORKSPACE_TYPE => UserPermissionTypes::ASSETS->value,
        DataObjectWorkspace::WORKSPACE_TYPE => UserPermissionTypes::OBJECTS->value,
        DocumentWorkspace::WORKSPACE_TYPE => UserPermissionTypes::DOCUMENTS->value,
    ];

    public function __construct(
        private QueryServiceInterface $workspaceQueryService,
        private UserPermissionServiceInterface $userPermissionService
    ) {
    }

    public function getWorkspaceQuery(?User $user, string $permission): BoolQuery
    {
        $boolQuery = new BoolQuery();
        if ($user?->isAdmin()) {
            return $boolQuery;
        }

        $boolQuery->addCondition(
            ConditionType::FILTER->value,
            $this->getAllowedElementTypesFilter($user)->toArrayAsSubQuery()
        );

        $workspacesQuery = new BoolQuery();
        foreach (array_keys(self::WORKSPACE_PERMISSION_MAPPING) as $workspaceType) {
            $workspaceQuery = new BoolQuery([
                ConditionType::FILTER->value => [
                    new TermFilter(
                        SystemField::ELEMENT_TYPE->getPath(),
                        ElementType::fromShortValue($workspaceType)->value
                    ),
                    $this->workspaceQueryService->getWorkspaceQuery($workspaceType, $user, $permission),
                ],
            ]);

            $workspacesQuery->addCondition(ConditionType::SHOULD->value, $workspaceQuery->toArray(true));
        }

        if (!$workspacesQuery->isEmpty()) {
            $boolQuery->addCondition(ConditionType::MUST->value, $workspacesQuery->toArray(true));
        }

        return $boolQuery;
    }

    private function getAllowedElementTypesFilter(User $user): TermsFilter
    {
        $allowedElementTypes = [];
        foreach (self::WORKSPACE_PERMISSION_MAPPING as $workspaceType => $permission) {
            if ($this->userPermissionService->hasPermission($user, $permission)) {
                $allowedElementTypes[] = ElementType::fromShortValue($workspaceType)->value;
            }
        }

        return new TermsFilter(SystemField::ELEMENT_TYPE->getPath(), $allowedElementTypes);
    }
}
