<?php

namespace Tests\MoneyProblem;

use MoneyProblem\Domain\Currency;
use MoneyProblem\Domain\InvalidExchangeCurrencyException;
use MoneyProblem\Domain\InvalidExchangeRateException;
use MoneyProblem\Domain\MissingExchangeRateException;
use MoneyProblem\Domain\Money;
use PHPUnit\Framework\TestCase;

class BankTest extends TestCase
{
    public function test_set_EUR_in_pivot_currency(){
        $bank = BankBuilder::aBank()
        ->withPivotCurency(Currency::EUR())
        ->withExancheRate(1.2, Currency::USD())
        ->build();
        $this->assertEquals(Currency::EUR(), $bank->getPivotCurrency());
    }

    public function test_convert_different_currency()
    {
        // arrange
        $bank = BankBuilder::aBank()
            ->withPivotCurency(Currency::EUR())
            ->withExancheRate(1.2, Currency::USD())
            ->build();
        $moneyFrom = new Money(12, Currency::USD());
        // act 
        $moneyTo = $bank->convert($moneyFrom, Currency::EUR());
        // assert
        $this->assertEquals(10, $moneyTo->getAmount());
    }

    public function test_convert_different_currency_and_go_back()
    {
        // arrange
        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(1.2, Currency::USD())->build();
        $moneyFrom = new Money(10, Currency::EUR());
        // act 
        $moneyTo = $bank->convert($moneyFrom, Currency::USD());
        // assert
        $this->assertEquals(12, $moneyTo->getAmount());
        $moneyFrom = new Money($moneyTo->getAmount(), Currency::USD());
        $moneyTo = $bank->convert($moneyFrom, Currency::EUR());
        $this->assertEquals(10, $moneyTo->getAmount());
    }

    public function test_round_tripping_in_same_currency(){
        $bank = BankBuilder::aBank()
                ->withPivotCurency(Currency::EUR())
                ->withExancheRate(0.12548966, Currency::KRW())
                ->build();
        $moneyKRW = new Money(10.254, Currency::KRW());
        $moneyEUR = $bank->convert($moneyKRW, Currency::EUR());
        $moneyKRWConverted = $bank->convert($moneyEUR, Currency::KRW());
        // On test la marge d'erreur de 1%
        $this->assertGreaterThanOrEqual(Tools::getMinusOnePercent($moneyKRW->getAmount()), $moneyKRWConverted->getAmount());
        $this->assertLessThanOrEqual(Tools::getPlusOnePercent($moneyKRW->getAmount()), $moneyKRWConverted->getAmount());
    }

    public function test_round_tripping_with_different_currency(){
        $bank = BankBuilder::aBank()
                ->withPivotCurency(Currency::EUR())
                ->withExancheRate(1.2, Currency::USD())
                ->withExancheRate(1344, Currency::KRW())
                ->build();
        $moneyKRW = new Money(15, Currency::KRW());
        $moneyUSD = $bank->convert($moneyKRW, Currency::USD());
        $moneyConvertedKRW = $bank->convert($moneyUSD, Currency::KRW());
        // On test la marge d'erreur de 1%
        $this->assertGreaterThanOrEqual(Tools::getMinusOnePercent($moneyKRW->getAmount()), $moneyConvertedKRW->getAmount());
        $this->assertLessThanOrEqual(Tools::getPlusOnePercent($moneyKRW->getAmount()), $moneyConvertedKRW->getAmount());
    }

    public function test_round_tripping_with_different_currency_with_less_rate(){
        $bank = BankBuilder::aBank()
                ->withPivotCurency(Currency::EUR())
                ->withExancheRate(1.235944, Currency::USD())
                ->withExancheRate(0.12548966, Currency::KRW())
                ->build();
        $moneyKRW = new Money(10.254, Currency::KRW());
        $moneyUSD = $bank->convert($moneyKRW, Currency::USD());
        $moneyConvertedKRW = $bank->convert($moneyUSD, Currency::KRW());
        // On test la marge d'erreur de 1%
        $this->assertGreaterThanOrEqual(Tools::getMinusOnePercent($moneyKRW->getAmount()), $moneyConvertedKRW->getAmount());
        $this->assertLessThanOrEqual(Tools::getPlusOnePercent($moneyKRW->getAmount()), $moneyConvertedKRW->getAmount());
    }

    public function test_convert_into_same_currency()
    {
        // arrange 
        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(1.2, Currency::USD())->build();
        $moneyFrom = new Money(10, Currency::EUR());
        // act 
        $moneyTo = $bank->convert($moneyFrom, Currency::EUR());
        // assert
        $this->assertEquals(10, $moneyTo->getAmount());
    }

    public function test_convert_throws_exception_when_rate_is_unknow()
    {
        $this->expectException(MissingExchangeRateException::class);
        $this->expectExceptionMessage('EUR->KRW');

        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(1.2, Currency::USD())->build();
        $moneyFrom = new Money(10, Currency::EUR());
        $bank->convert($moneyFrom, Currency::KRW());
    }

    public function test_convert_without_exchange_rate()
    {
        $this->expectException(MissingExchangeRateException::class);
        $this->expectExceptionMessage('KRW->USD');

        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(1.2, Currency::USD())->build();
        $moneyFrom = new Money(10, Currency::KRW());
        $bank->convert($moneyFrom, Currency::USD());
    }

    public function test_create_bank_with_EUR_in_pivot_and_add_EUR_in_rates(){
        $this->expectException(InvalidExchangeCurrencyException::class);
        $this->expectExceptionMessage("Can't add an exchange rate with the pivot currency");

        $bank = BankBuilder::aBank()
        ->withPivotCurency(Currency::EUR())
        ->withExancheRate(1, Currency::EUR())
        ->build();
        $this->assertEquals(Currency::EUR(), $bank->getPivotCurrency());
    }

    public function test_add_exchange_rate_equals_0()
    {
        $this->expectException(InvalidExchangeRateException::class);
        $this->expectExceptionMessage("The exchange rate should be greater than 0");

        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(0, Currency::USD())->build();
        $moneyFrom = new Money(10, Currency::EUR());
        $bank->convert($moneyFrom, Currency::KRW());
    }

    public function test_add_exchange_rate_less_than_0()
    {
        $this->expectException(InvalidExchangeRateException::class);
        $this->expectExceptionMessage("The exchange rate should be greater than 0");

        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(-1, Currency::USD())->build();
        $moneyFrom = new Money(10, Currency::EUR());
        $bank->convert($moneyFrom, Currency::KRW());
    }

    public function test_add_exchange_rate_KRW_1_298989888()
    {
        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(1.298989888, Currency::KRW())->build();
        $moneyFrom = new Money(10,Currency::EUR());
        $moneyTo = $bank->convert($moneyFrom, Currency::KRW());
        $this->assertEquals(12.9899,$moneyTo->getAmount()); 
    }

    public function test_add_exchange_rate_KRW_0_0000001455()
    {
        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(0.0000001455, Currency::KRW())->build();
        $moneyFrom = new Money(10,Currency::EUR());
        $moneyTo = $bank->convert($moneyFrom, Currency::KRW());
        $this->assertEquals(0.00001,$moneyTo->getAmount());
    }

    public function test_convert_EUR_to_EUR_with_USD_in_pivot()
    {
        $bank = BankBuilder::aBank()->withPivotCurency(Currency::USD())->withExancheRate(1.2, Currency::EUR())->build();
        $moneyFrom = new Money(10, Currency::EUR());
        $moneyTo = $bank->convert($moneyFrom, Currency::EUR());
        $this->assertEquals(10, $moneyTo->getAmount());
    }

    public function test_convert_with_different_exchange_rates()
    {
        $bank = BankBuilder::aBank()->withPivotCurency(Currency::EUR())->withExancheRate(1.2, Currency::USD())->build();
        $bank = $bank->addRate(Currency::USD(), 1.3);
        $moneyFrom = new Money(10, Currency::EUR());
        $moneyTo = $bank->convert($moneyFrom, Currency::USD());
        $this->assertEquals(13, $moneyTo->getAmount());
    }
}
