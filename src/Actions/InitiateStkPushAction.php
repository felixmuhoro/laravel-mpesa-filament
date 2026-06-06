<?php

namespace FelixMuhoro\MpesaFilament\Actions;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use FelixMuhoro\Mpesa\Facades\Mpesa;
use Illuminate\Support\Facades\Log;

class InitiateStkPushAction extends Action
{
    public static function make(?string  = null): static
    {
        /** @var static  */
         = parent::make( ?? "initiate_stk_push");

        return 
            ->label("STK Push")
            ->icon("heroicon-o-device-phone-mobile")
            ->color("primary")
            ->modalHeading("Initiate M-Pesa STK Push")
            ->modalDescription("Send a payment request prompt directly to the customer\'s phone.")
            ->modalWidth("md")
            ->form([
                TextInput::make("phone_number")
                    ->label("Phone Number")
                    ->placeholder("254712345678")
                    ->tel()
                    ->required()
                    ->helperText("Format: 254XXXXXXXXX (no leading +)")
                    ->rules(["regex:/^254[0-9]{9}$/"]),

                TextInput::make("amount")
                    ->label("Amount (KES)")
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(150000)
                    ->required()
                    ->prefix("KES"),

                TextInput::make("account_reference")
                    ->label("Account Reference")
                    ->default("Payment")
                    ->maxLength(12)
                    ->required(),

                TextInput::make("transaction_desc")
                    ->label("Description")
                    ->default("Payment")
                    ->maxLength(13),
            ])
            ->action(function (array , ?object ): void {
                try {
                         = ["phone_number"];
                        = (int) ["amount"];
                     = ["account_reference"] ?? "Payment";
                          = ["transaction_desc"]  ?? "Payment";

                     = Mpesa::stkPush(, , , );

                    if (isset(["ResponseCode"]) && ["ResponseCode"] === "0") {
                        Notification::make()
                            ->title("STK Push Sent")
                            ->body("The payment request has been sent to " .  . ". CheckoutRequestID: " . (["CheckoutRequestID"] ?? "N/A"))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title("STK Push Failed")
                            ->body(["errorMessage"] ?? ["ResponseDescription"] ?? "Unknown error from Safaricom.")
                            ->danger()
                            ->send();
                    }
                } catch (\Throwable ) {
                    Log::error("MpesaFilament STK Push error", ["error" => ->getMessage()]);

                    Notification::make()
                        ->title("Error")
                        ->body("Could not initiate STK Push: " . ->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
