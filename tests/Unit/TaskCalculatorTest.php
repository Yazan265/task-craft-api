<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase; // لاحظ: هنا نستخدم PHPUnit المباشر وليس لارافل كاملاً!
use App\Helpers\TaskCalculator;
use PHPUnit\Framework\Attributes\Test;

class TaskCalculatorTest extends TestCase
{
    #[Test] // بديل أنظف وأحدث للتعليق القديم
    public function it_calculates_correct_completion_percentage()
    {
        $calculator = new TaskCalculator();

        // تجربة: 5 مهام مكتملة من أصل 10 مهام -> المفروض 50%
        $result = $calculator->calculateCompletionPercentage(5, 10);

        // جزم ونفي: هل النتيجة تساوي 50.0؟
        $this->assertEquals(50.0, $result);
    }

    #[Test] // بديل أنظف وأحدث للتعليق القديم
    public function it_returns_zero_if_total_tasks_is_zero()
    {
        $calculator = new TaskCalculator();

        // تجربة قسمة على صفر لعدم حدوث خطأ
        $result = $calculator->calculateCompletionPercentage(0, 0);

        $this->assertEquals(0.0, $result);
    }
}