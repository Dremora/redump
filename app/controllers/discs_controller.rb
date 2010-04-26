class DiscsController < ApplicationController
  def index
    @discs = Disc.all
  end

  def show
    @disc = Disc.find(params[:id])
  end

  def new
    @disc = Disc.new
  end

  def edit
    @disc = Disc.find(params[:id])
  end

  def create
    @disc = Disc.new(params[:disc])

    if @disc.save
      redirect_to(@disc, :notice => 'Disc was successfully created.')
    else
      render :action => "new"
    end
  end

  def update
    @disc = Disc.find(params[:id])

    if @disc.update_attributes(params[:disc])
      redirect_to(@disc, :notice => 'Disc was successfully updated.')
    else
      render :action => "edit"
    end
  end

  def destroy
    @disc = Disc.find(params[:id])
    @disc.destroy

    redirect_to(discs_url)
  end
end
