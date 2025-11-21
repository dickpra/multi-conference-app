<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Sdg: int implements HasLabel
{
    case NoPoverty = 1;
    case ZeroHunger = 2;
    case GoodHealth = 3;
    case QualityEducation = 4;
    case GenderEquality = 5;
    case CleanWater = 6;
    case CleanEnergy = 7;
    case DecentWork = 8;
    case IndustryInnovation = 9;
    case ReducedInequalities = 10;
    case SustainableCities = 11;
    case ResponsibleConsumption = 12;
    case ClimateAction = 13;
    case LifeBelowWater = 14;
    case LifeOnLand = 15;
    case PeaceJustice = 16;
    case Partnerships = 17;
    case General = 18; // Opsi tambahan dari request Anda

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NoPoverty => '1. No Poverty',
            self::ZeroHunger => '2. Zero Hunger',
            self::GoodHealth => '3. Good Health and Well-being',
            self::QualityEducation => '4. Quality Education',
            self::GenderEquality => '5. Gender Equality',
            self::CleanWater => '6. Clean Water and Sanitation',
            self::CleanEnergy => '7. Affordable and Clean Energy',
            self::DecentWork => '8. Decent Work and Economic Growth',
            self::IndustryInnovation => '9. Industry, Innovation and Infrastructure',
            self::ReducedInequalities => '10. Reduced Inequalities',
            self::SustainableCities => '11. Sustainable Cities and Communities',
            self::ResponsibleConsumption => '12. Responsible Consumption and Production',
            self::ClimateAction => '13. Climate Action',
            self::LifeBelowWater => '14. Life Below Water',
            self::LifeOnLand => '15. Life on Land',
            self::PeaceJustice => '16. Peace, Justice and Strong Institutions',
            self::Partnerships => '17. Partnerships for the Goals',
            self::General => '18. Sustainable Development Goals (General)',
        };
    }

    public function getColor(): string
    {
        // Opsional: Memberikan warna badge berbeda
        return match ($this) {
            self::NoPoverty, self::ZeroHunger, self::GoodHealth => 'danger',
            self::QualityEducation, self::GenderEquality, self::CleanWater => 'warning',
            self::CleanEnergy, self::DecentWork, self::IndustryInnovation => 'primary',
            self::ClimateAction, self::LifeBelowWater, self::LifeOnLand => 'success',
            default => 'info',
        };
    }

    public static function tryFromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->getLabel() === $label) {
                return $case;
            }
        }
        return null;
    }
}