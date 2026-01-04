/*---------------------------------------------------------------------------------------------
 *  Copyright (c) Hassan Tech. All rights reserved.
 *  Licensed under the Proprietary License. See LICENSE-HASSANIDE.txt for license information.
 *--------------------------------------------------------------------------------------------*/

import * as platform from '../../../../../base/common/platform.js';
import { ServicesAccessor } from '../../../../../platform/instantiation/common/instantiation.js';
import { IWorkbenchEnvironmentService } from '../../../../services/environment/common/environmentService.js';

export default function content(accessor: ServicesAccessor) {
	const isServerless = platform.isWeb && !accessor.get(IWorkbenchEnvironmentService).remoteAuthority;
	return `
## مرحباً بك في Hassan IDE - محرر الأكواد العربي
المحرر الأساسي في Hassan IDE مليء بالميزات المتقدمة. هذه الصفحة تبرز عدداً منها وتتيح لك تجربتها بشكل تفاعلي.

* [تحرير متعدد المؤشرات](#multi-cursor-editing) - تحديد كتلة، تحديد جميع التكرارات، إضافة مؤشرات إضافية والمزيد.
* [IntelliSense](#intellisense) - احصل على اقتراحات الكود والمعاملات لكودك والوحدات الخارجية.
* [إجراءات الأسطر](#line-actions) - انقل الأسطر بسرعة لإعادة ترتيب كودك.${!isServerless ? `
* [إعادة التسمية](#rename-refactoring) - أعد تسمية الرموز بسرعة عبر قاعدة الكود الخاصة بك.` : ''}
* [التنسيق](#formatting) - حافظ على مظهر كودك رائعاً مع التنسيق المدمج.
* [طي الكود](#code-folding) - ركز على الأجزاء الأكثر صلة بطي المناطق الأخرى.
* [الأخطاء والتحذيرات](#errors-and-warnings) - شاهد الأخطاء والتحذيرات أثناء الكتابة.
* [Snippets](#snippets) - وفر الوقت مع قصاصات الكود الجاهزة.
* [Emmet](#emmet) - دعم Emmet المدمج يرفع تحرير HTML و CSS لمستوى آخر.
* [فحص أنواع JavaScript](#javascript-type-checking) - فحص الأنواع على ملفات JavaScript باستخدام TypeScript بدون إعدادات.



### تحرير متعدد المؤشرات (Multi-Cursor Editing)
استخدام مؤشرات متعددة يتيح لك تحرير أجزاء متعددة من المستند في وقت واحد، مما يحسن إنتاجيتك بشكل كبير.

1. تحديد كتلة - اضغط <span class="mac-only windows-only">أي مجموعة من kb(cursorColumnSelectDown), kb(cursorColumnSelectRight), kb(cursorColumnSelectUp), kb(cursorColumnSelectLeft)</span> <span class="shortcut mac-only">|⇧⌥|</span><span class="shortcut windows-only linux-only">|Shift+Alt|</span>
2. إضافة مؤشر - اضغط kb(editor.action.insertCursorAbove) للإضافة فوق، أو kb(editor.action.insertCursorBelow) للإضافة تحت.
3. إنشاء مؤشرات على جميع تكرارات نص - حدد نص مثل |background-color| واضغط kb(editor.action.selectHighlights).

|||css
#p1 {background-color: #ff0000;}                /* أحمر بصيغة HEX */
#p2 {background-color: hsl(120, 100%, 50%);}    /* أخضر بصيغة HSL */
#p3 {background-color: rgba(0, 4, 255, 0.733);} /* أزرق بقناة ألفا */
|||

> **نصيحة CSS:** نوفر أيضاً عينات ألوان مضمنة لـ CSS، بالإضافة لعرض كيفية تمثيل العنصر في HTML عند التمرير فوقه.

### IntelliSense

Hassan IDE يأتي مع IntelliSense القوي لـ JavaScript و TypeScript مثبت مسبقاً. في المثال أدناه، ضع مؤشر النص بعد النقطة واضغط kb(editor.action.triggerSuggest) لتشغيل IntelliSense.

|||js
const canvas = document.querySelector('canvas');
const context = canvas.getContext('2d');

context.strokeStyle = 'blue';
context.
|||

>**نصيحة:** نحن نشحن دعم JavaScript و TypeScript جاهزاً، ويمكن ترقية اللغات الأخرى مع IntelliSense أفضل من خلال [الإضافات](command:workbench.extensions.action.showPopularExtensions).


### إجراءات الأسطر (Line Actions)
نظراً لأنه من الشائع جداً العمل مع النص الكامل في سطر، نوفر مجموعة من الاختصارات المفيدة:
1. <span class="mac-only windows-only">انسخ سطراً وأدرجه فوق أو تحت الموضع الحالي مع kb(editor.action.copyLinesDownAction) أو kb(editor.action.copyLinesUpAction).</span><span class="linux-only">انسخ السطر الحالي بالكامل عندما لا يوجد نص محدد مع kb(editor.action.clipboardCopyAction).</span>
2. انقل سطراً كاملاً أو مجموعة أسطر لأعلى أو لأسفل مع kb(editor.action.moveLinesUpAction) و kb(editor.action.moveLinesDownAction).
3. احذف السطر بالكامل مع kb(editor.action.deleteLines).

|||json
{
	"name": "أحمد",
	"age": 25,
	"city": "الرياض"
}
|||

>**نصيحة:** مهمة شائعة أخرى هي التعليق على كتلة من الكود - يمكنك تبديل التعليق بالضغط على kb(editor.action.commentLine).


${!isServerless ? `
### إعادة التسمية (Rename Refactoring)
من السهل إعادة تسمية رمز مثل اسم دالة أو متغير. اضغط kb(editor.action.rename) أثناء وجودك في الرمز |Book| لإعادة تسمية جميع النسخ - سيحدث هذا عبر جميع الملفات في المشروع.

|||js
// استدعاء الدالة
new Book("حرب العوالم", "هـ. ج. ويلز");
new Book("المريخي", "آندي وير");

/**
 * يمثل كتاباً.
 *
 * @param {string} title عنوان الكتاب
 * @param {string} author من كتب الكتاب
 */
function Book(title, author) {
	this.title = title;
	this.author = author;
}
|||

> **نصيحة JSDoc:** IntelliSense في Hassan IDE يستخدم تعليقات JSDoc لتقديم اقتراحات أغنى.

` : ''}

### التنسيق (Formatting)
الحفاظ على كودك منسقاً بشكل جيد أمر صعب بدون أداة جيدة. لحسن الحظ، Hassan IDE يوفر تنسيق المستند والتحديد المدمج.

|||js
const cars = ["Saab", "Volvo", "BMW"];

for (let i = 0; i < cars.length; i++) {
// قم بتعديل التنسيق هنا
const element = cars[i];

}
|||

>**نصيحة:** يمكنك إضافة منسق إضافي من [سوق الإضافات](command:workbench.extensions.action.showPopularExtensions).


### طي الكود (Code Folding)
في ملف كبير، غالباً ما يكون من المفيد طي أقسام من الكود لزيادة قابلية القراءة.

1. ضع المؤشر على السطر 1 واضغط kb(editor.fold) لطي الكود.
2. اضغط kb(editor.unfold) لإلغاء الطي.
3. يمكن أيضاً الطي باستخدام أيقونة الدلتا في الحاشية.

|||html
<div>
	<header>
		<ul>
			<li><a href=""></a></li>
			<li><a href=""></a></li>
		</ul>
	</header>
	<footer>
		<p></p>
	</footer>
</div>
|||

>**نصيحة:** الطي يعتمد على المسافة البادئة. لطي مستوى معين، استخدم kb(editor.foldLevel1) حتى kb(editor.foldLevel5).


### الأخطاء والتحذيرات (Errors and Warnings)
الأخطاء والتحذيرات مُبرَزة أثناء تحرير الكود مع خطوط متموجة. في المثال أدناه يمكنك رؤية عدد من أخطاء بناء الجملة.

|||js
// هذا الكود يحتوي على أخطاء
function test ( ){
    console.log(test);
}
|||

>**نصيحة:** انتقل بين الأخطاء في الملف الحالي مع kb(editor.action.marker.nextInFiles) و kb(editor.action.marker.prevInFiles).


### Snippets
يمكنك زيادة سرعتك بشكل كبير باستخدام snippets. ببساطة ابدأ الكتابة |try| وحدد |trycatch| من قائمة الاقتراحات.

|||js

|||

>**نصيحة:** يمكنك إضافة snippets مخصصة من [سوق الإضافات](command:workbench.extensions.action.showPopularExtensions). أو أنشئ خاصتك عبر **Preferences: Configure Snippets**.


### Emmet
Emmet يأخذ snippets لمستوى أعلى: يمكنك كتابة تعبيرات تشبه CSS يتم توسيعها ديناميكياً.

جرب كتابة |ul>li.item$*5| في المحرر أدناه واضغط kb(editor.emmet.action.expandAbbreviation).

|||html

|||

>**نصيحة:** [ورقة غش Emmet](https://docs.emmet.io/cheat-sheet/) مصدر رائع للبدء.


### فحص أنواع JavaScript
في بعض الأحيان، فحص الأنواع يمكن أن يلتقط الأخطاء التي قد لا تلاحظها. جرب إضافة |// @ts-check| في أعلى ملف JavaScript.

|||js
// @ts-check
let easy = true;
easy = 42;
|||

>**نصيحة:** يمكنك أيضاً تمكين فحص الأنواع لجميع ملفات JavaScript مع إعداد |"js/ts.implicitProjectConfig.checkJs": true|.


## شكراً لاستخدامك Hassan IDE!

إذا وصلت إلى هذا الحد، فقد لمست بعض ميزات التحرير في Hassan IDE. لكن لا تتوقف الآن :) لدينا الكثير من [التوثيق](https://hassanide.com/docs)، [الفيديوهات التعليمية](https://hassanide.com/tutorials) و[النصائح والحيل](https://hassanide.com/tips) التي ستساعدك على تعلم استخدام المنتج.

وبينما أنت هنا، إليك بعض الأشياء الإضافية التي يمكنك تجربتها:
- افتح [لوحة الأوامر](command:workbench.action.showCommands) واكتشف المزيد من الأوامر.
- ثبّت إضافات جديدة من [سوق الإضافات](command:workbench.extensions.action.showPopularExtensions).
- قم بتفعيل ترخيصك للحصول على جميع الميزات من [تفعيل الترخيص](command:hassanide.license.activate).
`;
}
