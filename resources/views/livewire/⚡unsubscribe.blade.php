<?php

use Livewire\Component;
use App\Models\Subscriber;

new class extends Component
{
    public function mount($email){
        $subscriber = Subscriber::where('email' , $email)->latest()->first();
        $subscriber->delete();
        session()->flash('unsubscribe-success', 'You have unsubscribed from our newsletter');
        $this->redirect(route('home'), navigate:true);
    }
};
?>

<div>
    
</div>