<?php

namespace App\Http\Controllers\Api;


use App\Models\Contact;
use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(){
        $contacts = Contact::paginate(20);
        return response()->json(['message' => 'Gelen Kutusu' ,'data'=>$contacts], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable',
            'subject' => 'nullable',
            'body' => 'nullable',
            'status' => 'required',
        ]);

        $validatedData['ip']=request()->ip();
        $contact = Contact::create($validatedData);
        return response()->json(['message' => 'Başarıyla Mesaj Gönderildi.','data'=> $contact], 200);
    }

    public function mailsend(Request $request)
 {
    $request->validate([
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
        'email' => 'required|email',
    ]);

    $subject = $request->input('subject');
    $message = $request->input('message');
    $email = $request->input('email');

     //Eğer mesaj boşsa hata döndür
    if (empty(trim($message))) {
        return response()->json(['message' => 'Mail Gönderme Başarısız: Mesaj içeriği boş'], 422);
    }

    $data = [
        'name' => 'Cengiz Boztepe', // Burada dinamik olarak kullanıcının adını alabilirsiniz
        'message' => $message,
    ];

    $test = 'Bu Test Mailidir...'; // İsteğe bağlı olarak başka bir veri ekleyebilirsiniz
    try {
        // Mail gönder
        Mail::to($email)->send(new ContactMail($subject, $data, $test));

        // Mail gönderme başarılı olursa
        return response()->json(['message' => 'Mail Gönderildi'], 200);
    } catch (\Exception $e) {
        // Mail gönderme başarısız olursa
        return response()->json(['message' => 'Mail Gönderme Başarısız', 'error' => $e->getMessage()], 500);
    }



 }

}
