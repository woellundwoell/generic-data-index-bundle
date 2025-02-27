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

namespace Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\DefaultSearch\ConditionType;
use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\DefaultSearch\WildcardFilterMode;

final class WildcardFilter extends BoolQuery implements AsSubQueryInterface
{
    public function __construct(
        private readonly string $field,
        private readonly string $term,
        private readonly WildcardFilterMode $defaultWildcardMode = WildcardFilterMode::BOTH,
        private readonly bool $caseInsensitive = true,
    ) {
        parent::__construct([
            ConditionType::FILTER->value => $this->getWildcardQueryArray(),
        ]);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function isCaseInsensitive(): bool
    {
        return $this->caseInsensitive;
    }

    public function toArrayAsSubQuery(): array
    {
        return $this->getWildcardQueryArray();
    }

    private function getWildcardQueryArray(): array
    {
        $term = $this->term;

        if ($term !== '' && !str_contains($term, '*')) {
            if ($this->defaultWildcardMode === WildcardFilterMode::BOTH) {
                $term = "*$term*";
            } elseif ($this->defaultWildcardMode === WildcardFilterMode::PREFIX) {
                $term = "*$term";
            } elseif ($this->defaultWildcardMode === WildcardFilterMode::SUFFIX) {
                $term = "$term*";
            }
        }

        return [
            'wildcard' => [
                $this->field => [
                    'value' => $term,
                    'case_insensitive' => $this->caseInsensitive,
                ],
            ],
        ];
    }
}
