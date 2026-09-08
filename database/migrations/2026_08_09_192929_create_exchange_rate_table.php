<?php declare(strict_types=1);

use App\Models\ExchangeRate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', static function (Blueprint $table) {
            $table->id();
            $table->string('currency_from');
            $table->string('currency_to');
            $table->decimal('rate', 14, 6);
            $table->date('date');
            $table->timestamps();
            $table->softDeletes(ExchangeRate::COLUMN_DELETED_AT);

            $table->unique(['currency_from', 'currency_to', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
