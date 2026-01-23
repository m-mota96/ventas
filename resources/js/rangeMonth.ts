interface MonthRange {
    first: string;
    last: string;
}

export const rangeMonth = (): MonthRange => {
    const current = new Date();
    const year    = current.getFullYear();
    const month   = current.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay  = new Date(year, month + 1, 0);

    const format = (date: Date): string => {
        const yyyy = date.getFullYear();
        const mm   = String(date.getMonth() + 1).padStart(2, '0');
        const dd   = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    };

    return {
        first: format(firstDay),
        last: format(lastDay)
    };
};