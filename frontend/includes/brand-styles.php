<style>
/* AlCorte arriba; logo y barbería debajo */
.brand-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 12px;
    min-width: 0;
}
.brand-alcorte-title {
    flex: 1 1 100%;
    margin: 0;
    font-size: 1.1rem;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    font-style: italic;
    line-height: 1.1;
}
.brand-alcorte-title span {
    color: #e4c49a;
    font-style: normal;
}
.brand-logo-wrap {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: transparent;
    padding: 0;
    border: 2px solid rgba(228, 196, 154, 0.4);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.brand-logo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    border-radius: 50%;
    background: transparent;
}
.brand-text {
    min-width: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.brand-business {
    margin: 0;
    font-size: 1.12rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 0.02em;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.brand-subline {
    margin: 0;
    font-size: 0.65rem;
    font-weight: 600;
    color: #e4c49a;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.brand-subline.brand-subline--muted {
    color: #9eb0cc;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.6rem;
}

/* Nav: logo + barbería en una fila bajo AlCorte */
.brand-header:not(.brand-header--center) {
    flex: 1;
    min-width: 0;
    padding-right: 8px;
}
.brand-header:not(.brand-header--center) .brand-logo-wrap {
    order: 2;
}
.brand-header:not(.brand-header--center) .brand-text {
    order: 3;
    flex: 1;
}

/* Login / centrado */
.brand-header--center {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 10px;
    margin-bottom: 28px;
}
.brand-header--center .brand-alcorte-title {
    flex: none;
    font-size: 1.45rem;
}
.brand-header--center .brand-logo-wrap {
    width: 64px;
    height: 64px;
    order: unset;
}
.brand-header--center .brand-text {
    align-items: center;
    order: unset;
}
.brand-header--center .brand-business {
    font-size: 1.2rem;
    white-space: normal;
    text-align: center;
}
.brand-header--center .brand-subline {
    white-space: normal;
    text-align: center;
}

/* Ticket */
.brand-block--ticket .brand-header {
    justify-content: center;
}
.brand-block--ticket .brand-alcorte-title {
    font-size: 1.2rem;
}
</style>
