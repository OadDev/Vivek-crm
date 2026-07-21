<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\EmailConversation;
use App\Models\EmailMessage;
use App\Models\Product;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            ['name' => 'Priya Sharma', 'company' => 'Nimbus Retail Pvt Ltd', 'email' => 'priya.sharma@nimbusretail.com', 'whatsapp' => '+919876543210', 'designation' => 'Procurement Manager', 'daysAgo' => 1, 'starred' => true, 'notes' => 'Prefers WhatsApp over email. Follow up every Monday.'],
            ['name' => 'Rohan Mehta', 'company' => 'Bluepeak Industries', 'email' => 'rohan.mehta@bluepeak.io', 'whatsapp' => '+919123456780', 'designation' => 'Operations Head', 'daysAgo' => 2, 'starred' => false, 'notes' => 'Key decision maker for bulk orders.'],
            ['name' => 'Neha Kulkarni', 'company' => 'Kulkarni & Sons Traders', 'email' => 'neha.k@kulkarnisons.in', 'whatsapp' => '+919090912345', 'designation' => 'Owner', 'daysAgo' => 9, 'starred' => false, 'notes' => 'Interested in quarterly contract, needs quote.'],
            ['name' => 'Arjun Verma', 'company' => 'Vertex Logistics', 'email' => 'arjun.verma@vertexlog.com', 'whatsapp' => '+919988776655', 'designation' => 'Supply Chain Lead', 'daysAgo' => 3, 'starred' => false, 'notes' => ''],
            ['name' => 'Sanya Kapoor', 'company' => 'Aurora Home Décor', 'email' => 'sanya@aurorahome.co', 'whatsapp' => '+919812345670', 'designation' => 'Founder', 'daysAgo' => 12, 'starred' => true, 'notes' => 'Referred by Priya Sharma.'],
            ['name' => 'Vikram Singh', 'company' => 'Singh Hardware Mart', 'email' => 'vikram.singh@singhhw.com', 'whatsapp' => '+919765432109', 'designation' => 'Purchase Executive', 'daysAgo' => 27, 'starred' => false, 'notes' => 'No response since last quarter.'],
            ['name' => 'Ananya Iyer', 'company' => 'Iyer Textiles', 'email' => 'ananya.iyer@iyertextiles.com', 'whatsapp' => '+919654321098', 'designation' => 'Merchandiser', 'daysAgo' => 0, 'starred' => false, 'notes' => ''],
            ['name' => 'Karan Malhotra', 'company' => 'Malhotra Steel Works', 'email' => 'karan@malhotrasteel.in', 'whatsapp' => '+919543210987', 'designation' => 'Director', 'daysAgo' => 4, 'starred' => true, 'notes' => 'VIP client — priority support.'],
            ['name' => 'Divya Nair', 'company' => 'Nair Exports', 'email' => 'divya.nair@nairexports.com', 'whatsapp' => '+919432109876', 'designation' => 'Export Manager', 'daysAgo' => 8, 'starred' => false, 'notes' => ''],
            ['name' => 'Aditya Rao', 'company' => 'Rao Constructions', 'email' => 'aditya.rao@raoconstruct.com', 'whatsapp' => '+919321098765', 'designation' => 'Site Manager', 'daysAgo' => 25, 'starred' => false, 'notes' => 'Project on hold.'],
        ];

        $contactModels = [];
        foreach ($contacts as $c) {
            $lastContactedAt = now()->subDays($c['daysAgo']);
            $contactModels[$c['email']] = Contact::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'company' => $c['company'],
                    'whatsapp' => $c['whatsapp'],
                    'designation' => $c['designation'],
                    'status' => Contact::computeStatusFromDate($lastContactedAt),
                    'is_starred' => $c['starred'],
                    'last_contacted_at' => $lastContactedAt,
                    'source' => 'manual',
                    'notes' => $c['notes'],
                ]
            );
        }

        $templates = [
            ['name' => 'Order Confirmation', 'message' => 'Hi {name}, this is {employee} from {company}. Your order has been confirmed and will be processed by {date}. Thank you for your business!'],
            ['name' => 'Payment Reminder', 'message' => 'Hello {name}, this is a friendly reminder from {company} that your payment is due on {date}. Please let us know if you have any questions. — {employee}'],
            ['name' => 'Sample Dispatch Notice', 'message' => 'Hi {name}, your requested samples have been dispatched from {company} today, {date}. Regards, {employee}.'],
            ['name' => 'Festive Greetings', 'message' => 'Dear {name}, warm festive greetings from all of us at {company}! Wishing you continued success. — {employee}'],
            ['name' => 'Follow-up After Meeting', 'message' => 'Hi {name}, thank you for your time today. As discussed, {company} will share the proposal by {date}. Best, {employee}'],
        ];

        $templateModels = [];
        foreach ($templates as $t) {
            $templateModels[$t['name']] = WhatsappTemplate::updateOrCreate(['name' => $t['name']], $t);
        }

        WhatsappMessage::updateOrCreate(
            ['recipient_number' => $contactModels['priya.sharma@nimbusretail.com']->whatsapp, 'contact_id' => $contactModels['priya.sharma@nimbusretail.com']->id],
            [
                'whatsapp_template_id' => $templateModels['Order Confirmation']->id,
                'recipient_name' => 'Priya Sharma',
                'message' => $templateModels['Order Confirmation']->render(['name' => 'Priya', 'employee' => 'Vivek']),
                'sent_at' => now()->subDays(3),
            ]
        );

        $products = [
            ['code' => 'PRD-1001', 'name' => 'Stainless Steel Hinge', 'size' => '4 inch', 'weight' => '250g', 'unit' => 'PCS', 'rate' => 145.00, 'specification' => 'SS304, Matte Finish'],
            ['code' => 'PRD-1002', 'name' => 'Brass Door Handle', 'size' => '6 inch', 'weight' => '400g', 'unit' => 'PCS', 'rate' => 320.00, 'specification' => 'Antique Brass, Polished'],
            ['code' => 'PRD-1003', 'name' => 'Galvanized Iron Bracket', 'size' => '8 inch', 'weight' => '600g', 'unit' => 'BOX', 'rate' => 980.00, 'specification' => 'GI Coated, Pack of 10'],
            ['code' => 'PRD-1004', 'name' => 'Aluminium Window Track', 'size' => '2m', 'weight' => '1.2kg', 'unit' => 'PCS', 'rate' => 560.00, 'specification' => 'Anodized Aluminium'],
            ['code' => 'PRD-1005', 'name' => 'Chrome Cabinet Knob', 'size' => '2 inch', 'weight' => '80g', 'unit' => 'PCS', 'rate' => 65.00, 'specification' => 'Chrome Plated Zinc'],
            ['code' => 'PRD-1006', 'name' => 'PVC Pipe Clamp', 'size' => '1 inch', 'weight' => '35g', 'unit' => 'KG', 'rate' => 210.00, 'specification' => 'UV Resistant PVC'],
            ['code' => 'PRD-1007', 'name' => 'Decorative Wall Hook Set', 'size' => 'Standard', 'weight' => '150g', 'unit' => 'SET', 'rate' => 275.00, 'specification' => 'Set of 4, Matte Black'],
            ['code' => 'PRD-1008', 'name' => 'Heavy Duty Padlock', 'size' => '50mm', 'weight' => '220g', 'unit' => 'PCS', 'rate' => 190.00, 'specification' => 'Hardened Steel Shackle'],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['code' => $p['code']], $p);
        }

        $conv1 = EmailConversation::updateOrCreate(
            ['sender_email' => 'priya.sharma@nimbusretail.com', 'subject' => 'Re: Q3 Bulk Order Quotation'],
            [
                'contact_id' => $contactModels['priya.sharma@nimbusretail.com']->id,
                'sender_name' => 'Priya Sharma',
                'preview' => 'Thanks for the quick turnaround, could we discuss the payment terms for...',
                'folder' => 'inbox',
                'is_read' => false,
                'is_starred' => true,
                'last_message_at' => now()->subHours(2),
            ]
        );
        EmailMessage::updateOrCreate(['email_conversation_id' => $conv1->id, 'from_name' => 'Priya Sharma'], [
            'direction' => 'incoming',
            'to_name' => 'me',
            'body' => 'Hi team,<br><br>Thanks for the quick turnaround on the quotation. Could we discuss the payment terms for the Q3 bulk order? We were hoping for a 30-day credit period given our long-standing relationship.<br><br>Looking forward to your response.<br><br>Best,<br>Priya',
            'sent_at' => now()->subHours(2),
        ]);
        EmailMessage::updateOrCreate(['email_conversation_id' => $conv1->id, 'from_name' => 'Vivek Jain'], [
            'direction' => 'outgoing',
            'to_name' => 'Priya Sharma',
            'body' => 'Hello Priya,<br><br>Please find attached the quotation for the Q3 bulk order as discussed. Let us know if you need any adjustments.<br><br>Regards,<br>Vivek',
            'sent_at' => now()->subDay(),
        ]);

        $conv2 = EmailConversation::updateOrCreate(
            ['sender_email' => 'rohan.mehta@bluepeak.io', 'subject' => 'Shipment delay for order #BP-2291'],
            [
                'contact_id' => $contactModels['rohan.mehta@bluepeak.io']->id,
                'sender_name' => 'Rohan Mehta',
                'preview' => 'We noticed the shipment for order BP-2291 hasn\'t moved in 3 days...',
                'folder' => 'inbox',
                'is_read' => false,
                'is_starred' => false,
                'last_message_at' => now()->subHours(4),
            ]
        );
        EmailMessage::updateOrCreate(['email_conversation_id' => $conv2->id, 'from_name' => 'Rohan Mehta'], [
            'direction' => 'incoming',
            'to_name' => 'me',
            'body' => 'Hi,<br><br>We noticed the shipment for order #BP-2291 hasn\'t moved in 3 days according to the tracking portal. Could you please check with logistics and update us?<br><br>Thanks,<br>Rohan',
            'sent_at' => now()->subHours(4),
        ]);

        $conv3 = EmailConversation::updateOrCreate(
            ['sender_email' => 'sameer.joshi@newventure.co', 'subject' => 'Inquiry about product catalog'],
            [
                'contact_id' => null,
                'sender_name' => 'Sameer Joshi',
                'preview' => 'Hello, I came across your company and would love to know more about...',
                'folder' => 'inbox',
                'is_read' => false,
                'is_starred' => false,
                'last_message_at' => now()->subHours(6),
            ]
        );
        EmailMessage::updateOrCreate(['email_conversation_id' => $conv3->id, 'from_name' => 'Sameer Joshi'], [
            'direction' => 'incoming',
            'to_name' => 'me',
            'body' => 'Hello,<br><br>I came across your company and would love to know more about your product catalog, especially the hardware fittings range. Could you share a price list?<br><br>Regards,<br>Sameer',
            'sent_at' => now()->subHours(6),
        ]);

        $conv4 = EmailConversation::updateOrCreate(
            ['sender_email' => 'karan@malhotrasteel.in', 'subject' => 'Annual contract renewal'],
            [
                'contact_id' => $contactModels['karan@malhotrasteel.in']->id,
                'sender_name' => 'Karan Malhotra',
                'preview' => 'As discussed on call, sending over the renewal terms for your review...',
                'folder' => 'inbox',
                'is_read' => true,
                'is_starred' => false,
                'last_message_at' => now()->subDays(2),
            ]
        );
        EmailMessage::updateOrCreate(['email_conversation_id' => $conv4->id, 'from_name' => 'Karan Malhotra'], [
            'direction' => 'incoming',
            'to_name' => 'me',
            'body' => 'Hi Team,<br><br>As discussed on call, sending over the renewal terms for your review. Please confirm by Friday so we can proceed.<br><br>Regards,<br>Karan',
            'sent_at' => now()->subDays(2),
        ]);

        $conv5 = EmailConversation::updateOrCreate(
            ['sender_email' => 'arjun.verma@vertexlog.com', 'subject' => 'Re: Transport rate revision'],
            [
                'contact_id' => $contactModels['arjun.verma@vertexlog.com']->id,
                'sender_name' => 'Arjun Verma',
                'preview' => 'Noted the new rates, will circulate internally and revert by EOD.',
                'folder' => 'archive',
                'is_read' => true,
                'is_starred' => false,
                'last_message_at' => now()->subDays(3),
            ]
        );
        EmailMessage::updateOrCreate(['email_conversation_id' => $conv5->id, 'from_name' => 'Arjun Verma'], [
            'direction' => 'incoming',
            'to_name' => 'me',
            'body' => 'Noted the new rates, will circulate internally and revert by EOD.',
            'sent_at' => now()->subDays(3),
        ]);

        Activity::log('Demo data loaded during setup', 'bi-stars', 'info');
    }
}
