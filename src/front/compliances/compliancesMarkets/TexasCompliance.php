<?php

namespace SpatialMatchIdx\front\compliances\compliancesMarkets;

use SpatialMatchIdx\core\compliances\ComplianceInterface;
use SpatialMatchIdx\models\ComplianceSettingsModel;

class TexasCompliance implements ComplianceInterface
{
    const TREC_FORM = SPATIALMATCH_IDX_URL . 'resources/files/compliances/texas_trec.pdf';
    const IABS_FORM = SPATIALMATCH_IDX_URL . 'resources/files/compliances/texas_iabs.pdf';

    public function getMarkets(): array
    {
        return [
            'actris' => self::class,
            'txccmls' => self::class,
            'ctxmls' => self::class,
            'tylertx' => self::class,
            'hlmls' => self::class,
            'harmls' => self::class,
            'laar' => self::class,
            'lubar' => self::class,
            'ntreis' => self::class,
            'rpaar' => self::class,
            'sabor' => self::class
        ];
    }

    public function getClass(): string
    {
        return self::class;
    }

    /**
     * Adds the texas compliance forms to the site footer
     *
     * @param $copyright
     * @return string
     */
    public function generateComplianceCopyrightHtml(): string
    {
        $complianceSettings = ComplianceSettingsModel::getData();

        $iabsForm = $complianceSettings->getAttribute('file_src');

        // first we add the standard Texas compliance form to the footer
        $trecLink = sprintf('<a class="trec-cpn" href="%s" title="Texas Real Estate Commission Consumer Protection Notice" target="_blank">TREC Consumer Protection Notice</a>', self::TREC_FORM);
        $copyright = ' ' . $trecLink;

        if (!empty($iabsForm))
        {
            $iabsLink = sprintf('<a class="trec-iabs" href="%s" title="Texas Real Estate Commission Information About Brokerage Services" target="_blank">Information About Brokerage Services</a>', $iabsForm);
            $copyright .= ' &nbsp;&nbsp; ' . $iabsLink;
        }

        return $copyright;
    }
}
