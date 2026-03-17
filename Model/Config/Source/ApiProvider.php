<?php
/**
 * API Provider source model for system config.
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ApiProvider implements OptionSourceInterface
{
    public const OPENAI = 'openai';

    /**
     * Return array of options for API Provider dropdown.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::OPENAI, 'label' => __('OpenAI')],
        ];
    }
}
