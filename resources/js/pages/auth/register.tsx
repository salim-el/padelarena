import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { PasswordInput } from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

interface RegisterForm {
    account_type: 'individual' | 'company';
    company_name: string;
    vat_number: string;
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<RegisterForm>({
        account_type: 'individual',
        company_name: '',
        vat_number: '',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Créer un compte" description="Entrez vos informations pour créer votre compte">
            <Head title="Inscription" />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <fieldset className="grid gap-2">
                        <legend className="mb-2 text-sm font-medium">Vous êtes</legend>
                        <div className="grid grid-cols-2 gap-2">
                            {(
                                [
                                    ['individual', 'Un particulier'],
                                    ['company', 'Une entreprise'],
                                ] as const
                            ).map(([value, label]) => (
                                <label
                                    key={value}
                                    className={`cursor-pointer rounded-lg border-2 p-3 text-center text-sm font-semibold has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-[#1D5C7A] ${
                                        data.account_type === value ? 'border-[#1D5C7A] bg-[#E6ECF2] text-[#17304A]' : 'border-[#DAD7CC] bg-white'
                                    }`}
                                >
                                    <input
                                        type="radio"
                                        name="account_type"
                                        value={value}
                                        checked={data.account_type === value}
                                        onChange={() => setData('account_type', value)}
                                        className="sr-only"
                                    />
                                    {label}
                                </label>
                            ))}
                        </div>
                    </fieldset>

                    {data.account_type === 'company' && (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="company_name">Dénomination de l’entreprise</Label>
                                <Input
                                    id="company_name"
                                    required
                                    autoComplete="organization"
                                    value={data.company_name}
                                    onChange={(e) => setData('company_name', e.target.value)}
                                    disabled={processing}
                                    placeholder="Atelier Nova SRL"
                                />
                                <InputError message={errors.company_name} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="vat_number">Numéro de TVA</Label>
                                <Input
                                    id="vat_number"
                                    required
                                    value={data.vat_number}
                                    onChange={(e) => setData('vat_number', e.target.value)}
                                    disabled={processing}
                                    placeholder="BE0123456789"
                                    aria-describedby="vat_help"
                                />
                                <p id="vat_help" className="text-xs text-[#5F6B78]">
                                    Il figure sur vos factures. Les factures électroniques entre entreprises belges passent par la comptabilité du
                                    club.
                                </p>
                                <InputError message={errors.vat_number} />
                            </div>
                        </>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="name">{data.account_type === 'company' ? 'Nom de la personne de contact' : 'Nom complet'}</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                            placeholder="Prénom et nom"
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Adresse e-mail</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            tabIndex={2}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                            placeholder="prenom.nom@exemple.be"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Mot de passe</Label>
                        <PasswordInput
                            id="password"
                            required
                            tabIndex={3}
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder="Mot de passe"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">Confirmer le mot de passe</Label>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            tabIndex={4}
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder="Confirmer le mot de passe"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Créer mon compte
                    </Button>
                </div>

                <div className="text-muted-foreground text-center text-sm">
                    Déjà un compte ?{' '}
                    <TextLink href={route('login')} tabIndex={6}>
                        Se connecter
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
