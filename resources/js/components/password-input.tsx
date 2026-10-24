import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { Eye, EyeOff } from 'lucide-react';
import * as React from 'react';

// Champ mot de passe avec un œil pour vérifier ce qu'on tape, repris de NBLogiTrack.
const PasswordInput = React.forwardRef<HTMLInputElement, Omit<React.ComponentProps<'input'>, 'type'>>(({ className, ...props }, ref) => {
    const [visible, setVisible] = React.useState(false);
    const label = visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe';

    return (
        <div className="relative">
            <Input ref={ref} type={visible ? 'text' : 'password'} className={cn('pr-10', className)} {...props} />
            <button
                type="button"
                onClick={() => setVisible(!visible)}
                aria-label={label}
                aria-pressed={visible}
                title={label}
                className="text-muted-foreground hover:text-foreground absolute inset-y-0 right-0 flex items-center px-3 transition-colors"
            >
                {visible ? <EyeOff className="size-4" aria-hidden /> : <Eye className="size-4" aria-hidden />}
            </button>
        </div>
    );
});
PasswordInput.displayName = 'PasswordInput';

export { PasswordInput };
