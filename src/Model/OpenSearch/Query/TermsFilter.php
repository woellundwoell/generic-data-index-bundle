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

namespace Pimcore\Bundle\GenericDataIndexBundle\Model\OpenSearch\Query;

use Pimcore\Bundle\GenericDataIndexBundle\Enum\SearchIndex\OpenSearch\ConditionType;

/**
 * @deprecated Will be removed in 2.0, please use
 * Pimcore\Bundle\GenericDataIndexBundle\Model\DefaultSearch\Query\TermsFilter instead
 */
final class TermsFilter extends BoolQuery implements AsSubQueryInterface
{
    public function __construct(
        private readonly string $field,
        /** @var (int|string)[] */
        private readonly array $terms,
    ) {
        parent::__construct([
            ConditionType::FILTER->value => [
                'terms' => [
                    $this->field => $this->terms,
                ],
            ],
        ]);
    }

    public function getField(): string
    {
        return $this->field;
    }

    /** @return (int|string)[] */
    public function getTerms(): array
    {
        return $this->terms;
    }

    public function toArrayAsSubQuery(): array
    {
        return [
            'terms' => [
                $this->field => $this->terms,
            ],
        ];
    }
}
