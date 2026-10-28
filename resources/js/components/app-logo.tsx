import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-md">
                <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-none font-semibold">
                    PadelArena
                    <span className="ml-0.5 inline-block size-1.5 bg-[#C8E64A] ring-1 ring-[#0E1B2C]/20" aria-hidden />
                </span>
            </div>
        </>
    );
}
