<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submitContact(ContactRequest $request)
    {
        $contactData = Contact::create([
            'name' => $request->input('data.attributes.name'),
            'email' => $request->input('data.attributes.email'),
            'company' => $request->input('data.attributes.company'),
            'phone' => $request->input('data.attributes.phone'),
            'message' => $request->input('data.attributes.message'),
        ]);

        return new ContactResource($contactData);
    }
}
