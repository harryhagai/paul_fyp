<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed ecommerce categories for phones, computers, and accessories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Smartphones',
                'description' => 'Simu janja za Android na iPhone kwa matumizi ya mawasiliano, internet, camera, apps na biashara.',
            ],
            [
                'name' => 'Feature Phones',
                'description' => 'Simu ndogo za kawaida zenye battery inayodumu muda mrefu, nzuri kwa kupiga/kupokea simu na SMS.',
            ],
            [
                'name' => 'Tablets',
                'description' => 'Vifaa vya screen kubwa kuliko simu kwa kusoma, kuangalia video, kuchora, kazi na matumizi ya shule/ofisi.',
            ],
            [
                'name' => 'Laptops',
                'description' => 'Kompyuta mpakato kwa matumizi ya kazi, masomo, biashara, programming, design na gaming.',
            ],
            [
                'name' => 'Desktop Computers',
                'description' => 'Kompyuta za mezani kwa ofisi, shule, biashara, gaming na kazi zinazohitaji nguvu zaidi.',
            ],
            [
                'name' => 'Computer Monitors',
                'description' => 'Screen za kompyuta kwa matumizi ya ofisi, gaming, CCTV, design na kuongeza display ya laptop/desktop.',
            ],
            [
                'name' => 'Computer Accessories',
                'description' => 'Vifaa vya kuongeza matumizi ya kompyuta kama mouse, keyboard, stands, adapters na cables.',
            ],
            [
                'name' => 'Phone Accessories',
                'description' => 'Vifaa vya simu kama cover, screen protector, chargers, earphones, holders na cables.',
            ],
            [
                'name' => 'Chargers & Adapters',
                'description' => 'Chaja za simu, laptop na vifaa vingine pamoja na adapter za fast charging na normal charging.',
            ],
            [
                'name' => 'USB Cables',
                'description' => 'Cables za kuchaji na kuhamisha data kama Type-C, Micro USB, Lightning na USB extension cables.',
            ],
            [
                'name' => 'Power Banks',
                'description' => 'Betri za kubeba zinazotumika kuchaji simu, tablets na vifaa vingine ukiwa mbali na umeme.',
            ],
            [
                'name' => 'Earphones & Headphones',
                'description' => 'Vifaa vya kusikilizia muziki, calls, gaming na online meetings kwa waya au Bluetooth.',
            ],
            [
                'name' => 'Bluetooth Speakers',
                'description' => 'Spika za wireless kwa muziki, matangazo, nyumbani, ofisini na matumizi ya nje.',
            ],
            [
                'name' => 'Smart Watches',
                'description' => 'Saa janja za kupima muda, notifications, fitness, heart rate na kuunganishwa na simu.',
            ],
            [
                'name' => 'Memory Cards',
                'description' => 'Kadi za kuhifadhi picha, video, music na files kwenye simu, camera na vifaa vingine.',
            ],
            [
                'name' => 'Flash Drives',
                'description' => 'USB flash disks kwa kuhifadhi na kuhamisha files kati ya computer, TV na vifaa vingine.',
            ],
            [
                'name' => 'External Hard Drives',
                'description' => 'Hard disk za nje kwa kuhifadhi data kubwa kama documents, videos, backups na software.',
            ],
            [
                'name' => 'SSD Drives',
                'description' => 'Storage za kasi zaidi kwa laptop/desktop ili kuongeza speed ya booting, apps na file transfer.',
            ],
            [
                'name' => 'Hard Disk Drives',
                'description' => 'Storage za kawaida kwa desktop/laptop kwa kuhifadhi files, documents, movies na backups.',
            ],
            [
                'name' => 'RAM / Memory',
                'description' => 'Memory za kuongeza performance ya laptop au desktop kwa kufanya multitasking iwe rahisi.',
            ],
            [
                'name' => 'Processors / CPUs',
                'description' => 'Vifaa vya msingi vya kuchakata data kwenye desktop au system unit.',
            ],
            [
                'name' => 'Motherboards',
                'description' => 'Board kuu ya kompyuta inayounganisha processor, RAM, storage na vifaa vingine.',
            ],
            [
                'name' => 'Graphics Cards / GPUs',
                'description' => 'Vifaa vya kuongeza uwezo wa graphics kwa gaming, video editing, design na 3D rendering.',
            ],
            [
                'name' => 'Computer Power Supplies',
                'description' => 'Power supply units kwa desktop zinazotoa umeme kwa components za ndani ya computer.',
            ],
            [
                'name' => 'Computer Cases',
                'description' => 'Makasha ya desktop yanayohifadhi motherboard, power supply, drives na components nyingine.',
            ],
            [
                'name' => 'Cooling Fans & Thermal Paste',
                'description' => 'Vifaa vya kupunguza joto kwenye computer kama fans, CPU coolers na thermal paste.',
            ],
            [
                'name' => 'Keyboards',
                'description' => 'Keyboard za computer kwa typing, gaming, office work na matumizi ya kawaida.',
            ],
            [
                'name' => 'Mouse',
                'description' => 'Mouse za wired au wireless kwa laptop na desktop, kwa matumizi ya kawaida, gaming na office.',
            ],
            [
                'name' => 'Mouse Pads',
                'description' => 'Pads za kuweka mouse kwa movement smooth, gaming na kulinda meza.',
            ],
            [
                'name' => 'Laptop Bags',
                'description' => 'Mabegi ya kubebea laptop na vifaa vyake kwa usalama wakati wa safari au shule/ofisi.',
            ],
            [
                'name' => 'Laptop Chargers',
                'description' => 'Chaja za laptop za brand mbalimbali kama Dell, HP, Lenovo, Acer, Asus na nyingine.',
            ],
            [
                'name' => 'Laptop Batteries',
                'description' => 'Betri za replacement kwa laptop ambazo battery zake zimeharibika au hazikai na charge.',
            ],
            [
                'name' => 'Laptop Screens',
                'description' => 'Screen za replacement kwa laptop zilizopasuka au kuharibika display.',
            ],
            [
                'name' => 'Phone Screens',
                'description' => 'Display za simu kwa ajili ya replacement ya screen zilizopasuka au kuharibika.',
            ],
            [
                'name' => 'Phone Batteries',
                'description' => 'Betri za simu za replacement kwa simu zinazozima haraka au hazikai na charge.',
            ],
            [
                'name' => 'Phone Covers & Cases',
                'description' => 'Cover za kulinda simu dhidi ya scratches, dust na damage inapodondoka.',
            ],
            [
                'name' => 'Screen Protectors',
                'description' => 'Vioo vya kulinda screen ya simu dhidi ya mipasuko, mikwaruzo na pressure.',
            ],
            [
                'name' => 'Camera Accessories',
                'description' => 'Vifaa vya camera kama tripods, memory cards, ring lights, lenses na camera cables.',
            ],
            [
                'name' => 'Tripods & Phone Holders',
                'description' => 'Vifaa vya kushikilia simu/camera wakati wa kurekodi video, livestream au kupiga picha.',
            ],
            [
                'name' => 'Ring Lights',
                'description' => 'Taa za kuongeza mwanga kwa video recording, TikTok, YouTube, photography na online meetings.',
            ],
            [
                'name' => 'Webcams',
                'description' => 'Camera za computer kwa video calls, online classes, meetings na livestreaming.',
            ],
            [
                'name' => 'Microphones',
                'description' => 'Mic za kurekodi sauti, podcast, meetings, gaming na content creation.',
            ],
            [
                'name' => 'Printers',
                'description' => 'Mashine za kuchapisha documents, picha, reports na kazi za ofisi/shule.',
            ],
            [
                'name' => 'Printer Inks & Toners',
                'description' => 'Wino na toner kwa printer za aina mbalimbali kwa ajili ya printing.',
            ],
            [
                'name' => 'Printer Accessories',
                'description' => 'Vifaa vya printer kama cartridges, USB cables, rollers na maintenance tools.',
            ],
            [
                'name' => 'Networking Devices',
                'description' => 'Vifaa vya network kama routers, switches, access points na Wi-Fi extenders.',
            ],
            [
                'name' => 'Routers',
                'description' => 'Vifaa vya kusambaza internet kwa Wi-Fi au LAN nyumbani, ofisini au biashara.',
            ],
            [
                'name' => 'Switches',
                'description' => 'Vifaa vya kuunganisha computers nyingi kwenye network moja kwa kutumia LAN cables.',
            ],
            [
                'name' => 'LAN Cables',
                'description' => 'Cables za network kwa kuunganisha router, switch, desktop, CCTV na vifaa vingine.',
            ],
            [
                'name' => 'Wi-Fi Adapters',
                'description' => 'Vifaa vya kuongeza uwezo wa Wi-Fi kwenye desktop au laptop isiyo na wireless nzuri.',
            ],
            [
                'name' => 'Software & Licenses',
                'description' => 'Software halali kama antivirus, operating systems, office tools na licenses nyingine.',
            ],
            [
                'name' => 'Antivirus & Security',
                'description' => 'Programu za kulinda computer dhidi ya virus, malware na online threats.',
            ],
            [
                'name' => 'Operating Systems',
                'description' => 'Mifumo ya uendeshaji kama Windows na Linux kwa laptop/desktop.',
            ],
            [
                'name' => 'Gaming Accessories',
                'description' => 'Vifaa vya gaming kama controllers, gaming mouse, gaming keyboard, headsets na pads.',
            ],
            [
                'name' => 'Game Controllers',
                'description' => 'Vifaa vya kuchezea games kwenye PC, laptop au consoles.',
            ],
            [
                'name' => 'CCTV & Security Devices',
                'description' => 'Camera za ulinzi, DVR/NVR, cables na vifaa vya security kwa nyumba au biashara.',
            ],
            [
                'name' => 'POS & Business Devices',
                'description' => 'Vifaa vya biashara kama barcode scanners, receipt printers, cash drawers na POS accessories.',
            ],
            [
                'name' => 'Repair Tools',
                'description' => 'Vifaa vya mafundi kama screwdrivers, opening tools, testers, soldering tools na cleaning kits.',
            ],
            [
                'name' => 'Cleaning Kits',
                'description' => 'Vifaa vya kusafishia simu, laptop, keyboard, screen na electronics nyingine.',
            ],
            [
                'name' => 'Used / Refurbished Devices',
                'description' => 'Simu, laptop au computer zilizotumika lakini zipo kwenye hali nzuri na bei nafuu.',
            ],
            [
                'name' => 'New Arrivals',
                'description' => 'Bidhaa mpya zilizoongezwa dukani hivi karibuni.',
            ],
            [
                'name' => 'Special Offers',
                'description' => 'Bidhaa zenye punguzo la bei, promotion au offer maalumu.',
            ],
        ];

        foreach ($categories as $categoryData) {
            $slug = Str::slug($categoryData['name']);

            $category = Category::query()
                ->where('name', $categoryData['name'])
                ->orWhere('slug', $slug)
                ->first();

            if (! $category) {
                $category = new Category;
            }

            if (! $category->public_id) {
                $category->public_id = (string) Str::uuid();
            }

            $category->fill([
                'name' => $categoryData['name'],
                'slug' => $slug,
                'description' => $categoryData['description'],
            ]);

            $category->save();
        }
    }
}
